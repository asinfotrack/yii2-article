module.exports = function(grunt) {

	grunt.initConfig({
		watch: {
			js: {
				files: ['assets/src/js/yii2_article.js'],
				tasks: ['buildJs']
			}
		},
		uglify: {
			article: {
				files: [{
					expand: true,
					cwd: 'assets/src/js',
					src: ['*.js', '!*.min.js'],
					dest: 'assets/src/js',
					ext: '.min.js'
				}]
			}
		}
	});

	//loading of npm tasks
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-uglify');

	//task definitions
	grunt.registerTask('buildJs', ['uglify:article']);

	grunt.registerTask('build', ['buildJs']);
	grunt.registerTask('default', ['watch']);

};
