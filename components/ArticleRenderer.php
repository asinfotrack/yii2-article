<?php
namespace asinfotrack\yii2\article\components;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use HTMLPurifier;
use asinfotrack\yii2\article\Module;
use asinfotrack\yii2\article\models\Article;
use yii\helpers\Url;

/**
 * The rendering component of the article module. It processes the contents of an article model into the
 * final output.
 *
 * If this component is accessed via the module, it gets its configuration in the following order:
 * 1. the local attribute default values
 * 2. the module default configuration
 * 3. the application configuration
 *
 * If instantiated directly, the configuration will be processed as follows:
 * 1. the local attribute default values
 * 2. the provided instance configuration
 *
 * To see what each of the configuration options does, read the detailed documentation of the attributes.
 *
 * @author Pascal Mueller, AS infotrack AG
 * @link http://www.asinfotrack.ch
 * @license AS infotrack AG license / MIT, see provided license file
 */
class ArticleRenderer extends \yii\base\Component
{

	/**
	 * @var string the regex used to identify placeholders and its params. Defaults
	 * to placeholders wrapped by square brackets with params separated by pipe-characters.
	 *
	 * Examples:
	 * - [article:canonicalName] article placeholder with one param of the value `canonicalName`
	 * - [img|http://mysite.com/img.jpg] possible image placeholder with url
	 * - [placeholder:myParamValueOne:myOtherParamValue] exemplary dummy placeholder
	 *
	 * WATCH OUT: if you change the placeholder config, you need to change this as well to match it!
	 */
	public $placeholderRegex = '/\{\{\w+[\w\d-]{0,}(\|[^\}]+)?\}\}/';

	/**
	 * @var array an array containing the config for working with placeholders. The following indexes
	 * must be present:
	 * - start: the string/chars to use as the placeholder start (default: `[`)
	 * - end: the string/chars to use as the placeholder end (default: `]`)
	 * - paramsDelimiter: the string/chars to use as the params delimiter end (default: `:`)
	 *
	 * WATCH OUT: if you change these settings, you also have to set a custom placeholder regex!
	 */
	public $placeholderConfig = ['start'=>'{{', 'end'=>'}}', 'paramsDelimiter'=>'|'];

	/**
	 * @var array an array of callbacks/closures indexed by the placeholder-name. Each
	 * closure should have the signature `function ($name, $params)` where name is the lowercase
	 * representation of the placeholder (eg `article`) and params is an array of parameters provided.
	 *
	 * If no custom entry for handling article-placeholders is provided, a default-handler will be used
	 * to allow nested article-rendering.
	 */
	public $placeholderCallbackMap = [];

	/**
	 * @var string|null if set, the article will be wrapped in a tag with this name. If set to `null`
	 * the article will not be wrapped
	 */
	public $articleTagName = 'article';

	/**
	 * @var array the options for the wrapping tag around the article. This is only relevant when
	 * `$articleTagName` is not `null`.
	 */
	public $articleTagOptions = [];

	/**
	 * @var bool if set to true, the following data-attributes will automatically be added to the
	 * `$articleTagOptions`:
	 *
	 * - data-id: the primary key of the article
	 * - data-canonical: the canonical of the article
	 * - data-depth: the current level of recursive article rendering (starting from 0)
	 *
	 * This is only relevant when `$articleTagName` is not `null`.
	 */
	public $addDataAttributesToArticleTagOptions = false;

	/**
	 * @var bool whether or not to render debug tags
	 */
	public $showDebugTags = false;

	/**
	 * @var \Closure optional callback for content post-processing. This can be used for final tweaks
	 * of the intro, before it gets rendered. The callback should have the signature
	 * `function ($intro)` and return a string with the processed intro content.
	 *
	 * Example: use this to process markdown within your intro or wrap it within tags, etc.
	 */
	public $introRenderCallback;

	/**
	 * @var \Closure optional callback for content post-processing. This can be used for final tweaks
	 * of the content, before it gets rendered. The callback should have the signature
	 * `function ($content)` and return a string with the processed content.
	 *
	 * Example: use this to process markdown within your content.
	 */
	public $contentRenderCallback;

	/**
	 * @var bool whether or not to render the title
	 */
	public $renderTitle = true;

	/**
	 * @var bool whether or not to render the titles of nested articles as well
	 */
	public $renderNestedTitles = false;

	/**
	 * @var bool whether or not to encode the title-contents
	 */
	public $encodeTitle = true;

	/**
	 * @var string the tag name of the title
	 */
	public $titleTag = 'h1';

	/**
	 * @var array the options for the title tag
	 */
	public $titleOptions = [];

	/**
	 * @var bool whether or not to render the published date
	 */
	public $renderPublishedAt = true;

	/**
	 * @var bool whether or not to render the published at of nested articles as well
	 */
	public $renderNestedPublishedAt = false;

	/**
	 * @var callable a callable for custom date formatting. If not set, the local default
	 * will be used, which renders an icon alongside the date in short format using the yii
	 * date formatter.
	 *
	 * The callable should have the signature `function ($timestamp)` and return a string.
	 */
	public $publishedAtFormatCallback;

	/**
	 * @var bool whether or not to use the purifier on the intro
	 * @see \yii\helpers\HtmlPurifier::process()
	 */
	public $purifyIntro = true;

	/**
	 * @var array the config to use for purifying the intro
	 * @see \yii\helpers\HtmlPurifier::process()
	 */
	public $purifyIntroConfig = [];

	/**
	 * @var bool whether or not to use the purifier on the content
	 * @see \yii\helpers\HtmlPurifier::process()
	 */
	public $purifyContent = true;

	/**
	 * @var array the aliases to replace with an absolute url
	 */
	public $aliases = [];

	/**
	 * @var array html attributes like href|src to search and replace the aliases with an absolute url
	 */
	public $attributeAliases = ['href', 'src'];

	/** @var HTMLPurifier */
	private $purifier;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();

		//validate config
		$this->validateConfig();

		$this->purifier = Module::getInstance()->purifier->getPurifierInstance();
	}

	/**
	 * Performs the rendering of an article
	 *
	 * @param integer|string|\asinfotrack\yii2\article\models\Article $article the article to render
	 * @param int $depth the current depth of recursive article rendering
	 * @return string the rendered content
	 * @throws \yii\base\InvalidConfigException
	 */
	public function render($article, $depth=0)
	{
		//try to fetch article if string was provided
		if (!($article instanceof Article)) {
			$article = call_user_func([Module::getInstance()->classMap['articleModel'], 'findOne'], $article);
			if ($article === null) {
				$msg = Yii::t('app', 'No article found with entry `{key}`', ['key'=>$article]);
				return Html::tag('span', $msg, ['class'=>'label label-danger']);
			}
		}

		//catch empty article
		if (empty($article->intro) && empty($article->content)) {
			$msg = Yii::t('app', 'Article `{article}` was found but is empty', [
				'article'=>$article->canonical,
			]);
			return Html::tag('span', $msg, ['class'=>'label label-warning']);
		}

		//prepare the individual parts
		$parts = [];
		if ($this->renderTitle && ($this->renderNestedTitles || $depth === 0) && !empty($article->title)) {
			$parts[] = $this->renderTitle($article->title);
		}
		if ($this->renderPublishedAt && ($this->renderNestedPublishedAt || $depth === 0) && !empty($article->published_at)) {
			$parts[] = $this->renderPublishedAt($article->published_at);
		}
		if (!empty($article->intro)) $parts[] = $this->renderIntro($article->intro);
		//here extract @web
		if (!empty($article->content)) $parts[] = $this->renderContent($article->content, $depth);

		//concat the parts, wrap them if necessary and return the resulting article code
		$finalContent = implode("\n", $parts);
		if ($this->showDebugTags) $finalContent = $this->wrapInDebugLabels($finalContent, $article, $depth);
		if ($this->articleTagName !== null) $finalContent = $this->wrapInArticleTag($finalContent, $article, $depth);
		return $finalContent;
	}

	/**
	 * Performs the rendering of the article title
	 *
	 * @param string $title the title of the article
	 * @return string the resulting title code
	 */
	protected function renderTitle($title)
	{
		$title = $this->encodeTitle ? Html::encode($title) : $title;
		return Html::tag($this->titleTag, $title, $this->titleOptions);
	}

	/**
	 * Performs the rendering of the article intro
	 *
	 * @param string $intro the intro of the article
	 * @return string the resulting intro code
	 */
	protected function renderIntro($intro)
	{
		if ($this->purifyIntro) {
			$intro = $this->purifier->purify($intro);
		}
		if (!empty($intro) && is_callable($this->introRenderCallback)) {
			$intro = call_user_func($this->introRenderCallback, $intro);
		}

		return $intro;
	}

	/**
	 * Performs the rendering of the article content including rendering of
	 * nested articles.
	 *
	 * @param string $content the raw content of the article
	 * @param int $depth the current depth of the article to render
	 * @return string the resulting content code
	 */
	protected function renderContent($content, $depth)
	{
		if (!empty($this->aliases)) {
			//load all allowed aliases into a string
			$aliasesOptions = implode('|', $this->aliases);
			$attributeAliases = implode('|', $this->attributeAliases);

			//create regex
			$regex = sprintf('/((%s)="(%s.*?)")/', $attributeAliases, $aliasesOptions);

			//get all matches
			$contentParts = [];
			preg_match_all($regex, $content, $contentParts);

			if (isset($contentParts[1])) {
				//replace content href|src with absolute paths
				foreach ($contentParts[1] as $contentPart) {
					$regex = sprintf('/(%s)="(%s.*?)"/', $attributeAliases, $aliasesOptions);

					$urls = [];
					preg_match_all($regex, $contentPart, $urls);

					if (!isset($urls[2]) || !isset($urls[1][0])  || !isset($urls[0][0])) continue;
					$url = Yii::getAlias($urls[2][0]);
					$absoluteUrl = Url::to($url);
					$strReplace = sprintf('%s="%s"', $urls[1][0], $absoluteUrl);
					$content = str_replace($urls[0][0], $strReplace, $content);
				}
			}
		}

		if ($this->purifyContent) {
			$content = $this->purifier->purify($content);
		}
		if (!empty($content) && is_callable($this->contentRenderCallback)) {
			$content = call_user_func($this->contentRenderCallback, $content);
		}
		$content = $this->replacePlaceholders($content, $depth);

		return $content;
	}

	/**
	 * Performs the rendering of the published at part
	 *
	 * @param int $publishedAt the timestamp of the publication date
	 * @return string the resulting code for the published at part
	 * @throws \yii\base\InvalidConfigException
	 */
	protected function renderPublishedAt($publishedAt)
	{
		if (is_callable($this->publishedAtFormatCallback)) {
			return call_user_func($this->publishedAtFormatCallback, $publishedAt);
		} else {
			return Html::tag('p', Yii::$app->formatter->asDate($publishedAt), ['class'=>'article-meta']);
		}
	}

	/**
	 * Pre- and suffixes the content with debug labels according to renderer config
	 *
	 * @param string $content the content to wrap
	 * @param \asinfotrack\yii2\article\models\Article $article the article model
	 * @param int $depth the current depth of the article to render
	 * @return string the content wrapped in tags
	 */
	protected function wrapInDebugLabels($content, $article, $depth)
	{
		$msgStart = Yii::t('app', 'ARTICLE BEGIN `{canonical}`, DEPTH {depth}', [
			'canonical'=>$article->canonical,
			'depth'=>$depth,
		]);
		$msgEnd = Yii::t('app', 'ARTICLE END `{canonical}', [
			'canonical'=>$article->canonical,
		]);

		return implode("\n", [
			Html::tag('span', $msgStart, ['class'=>'label label-info']),
			$content,
			Html::tag('span', $msgEnd, ['class'=>'label label-info']),
		]);
	}

	/**
	 * Wraps content in the article tags according to renderer config
	 *
	 * @param string $content the content to wrap
	 * @param \asinfotrack\yii2\article\models\Article $article the article model
	 * @param int $depth the current depth of the article to render
	 * @return string the content wrapped in tags
	 */
	protected function wrapInArticleTag($content, $article, $depth)
	{
		if ($this->addDataAttributesToArticleTagOptions) {
			if (!isset($this->articleTagOptions['data'])) $this->articleTagOptions['data'] = [];
			$this->articleTagOptions['data'] = ArrayHelper::merge($this->articleTagOptions['data'], [
				'id'=>$article->id,
				'canonical'=>$article->canonical,
				'depth'=>$depth,
			]);
		}

		return Html::tag($this->articleTagName, $content, $this->articleTagOptions);
	}

	/**
	 * Searches and replaces placeholders within a text
	 *
	 * @param string $string the text
	 * @param int $depth the current depth of recursive article rendering
	 * @return string the resulting string with the placeholders replaced
	 */
	protected function replacePlaceholders($string, $depth)
	{
		return preg_replace_callback($this->placeholderRegex, function ($match) use ($depth) {
			$trimmedMatch = ltrim(rtrim($match[0], $this->placeholderConfig['end']), $this->placeholderConfig['start']);
			$parts = explode($this->placeholderConfig['paramsDelimiter'], $trimmedMatch);

			return $this->handlePlaceholder($parts[0], count($parts) > 1 ? array_slice($parts, 1) : [], $depth);
		}, $string);
	}

	/**
	 * Handler for placeholder rendering.
	 *
	 * Special case: the `article`-placeholder is handled by default with a recursive render call
	 * if no custom callback is provided for it. This allows nesting of articles by default.
	 *
	 * @param string $name the name of the placeholder
	 * @param string[] $params the placeholder params
	 * @param int $depth the current depth of recursive article rendering
	 * @return string the resulting content of the placeholder
	 * @throws \yii\base\InvalidConfigException
	 */
	protected function handlePlaceholder($name, $params, $depth)
	{
		if (!isset($this->placeholderCallbackMap[$name])) {
			if (strcasecmp($name, 'article') === 0 && !empty($params)) {
				return $this->render($params[0], $depth+1);
			} else {
				$msg = Yii::t('app', 'Unhandled placeholder `{name}` with params `{params}`', [
					'name'=>$name,
					'params'=>empty($params) ? Yii::t('app', 'no params') : implode(', ', $params),
				]);
				return Html::tag('span', $msg, ['class'=>'label label-warning']);
			}
		}

		return call_user_func($this->placeholderCallbackMap[$name], $params);
	}

	/**
	 * Validates the config of the renderer instance
	 *
	 * @throws \yii\base\InvalidConfigException when config is not valid
	 */
	protected function validateConfig()
	{
		//validate properties
		if (empty($this->placeholderRegex)) {
			throw new InvalidConfigException(Yii::t('app', 'Placeholder regex must be set'));
		}
		if (empty($this->placeholderConfig)) {
			throw new InvalidConfigException(Yii::t('app', 'Placeholder config must be set'));
		}
		foreach (['start','end','paramsDelimiter'] as $key) {
			if (!isset($this->placeholderConfig[$key])) {
				$msg = Yii::t('app', 'Placeholder config must contain the key `{k}`', ['k'=>$key]);
				throw new InvalidConfigException($msg);
			}
		}

		//validate callbacks
		foreach ($this->placeholderCallbackMap as $placeholder=>$callback) {
			if (!is_callable($callback)) {
				$msg = Yii::t('app', 'The callback for the placeholder {ph} is not valid', ['ph'=>$placeholder]);
				throw new InvalidConfigException($msg);
			}
		}
		if ($this->introRenderCallback !== null && !is_callable($this->introRenderCallback)) {
			$msg = Yii::t('app','Callback {cb} is not valid', ['cb'=>'introRenderCallback']);
			throw new InvalidConfigException($msg);
		}
		if ($this->contentRenderCallback !== null && !is_callable($this->contentRenderCallback)) {
			$msg = Yii::t('app','Callback {cb} is not valid', ['cb'=>'contentRenderCallback']);
			throw new InvalidConfigException($msg);
		}
	}

}
