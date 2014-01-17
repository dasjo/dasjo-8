module.exports = function (grunt) {

  var default_tasks = [
    'sass_directory_import',
    'sass',
    'autoprefixer'
  ];

  grunt.initConfig({
    sass_directory_import: {
      scss: {
        files: {
          src: ['scss/**/_all.scss']
        },
      },
    },
    sass: {
      dist: {
        options: {
          style: 'expanded',
          debugInfo: true,
          sourcemap: true,
          compass: true
        },
        files: {
          'css/screen.css' : 'scss/screen.scss'
        }
      },
    },
    autoprefixer: {
      options: {
        browsers: ['last 1 version'] // more codenames at https://github.com/ai/autoprefixer#browsers
      },
      dist: {
        files: [{
          expand: true,
          cwd: 'css/',
          src: '{,*/}*.css',
          dest: 'css/'
        }]
      }
    },
    watch: {
      compass: {
        files: ['scss/{,*/}*.{scss,sass}'],
        tasks: default_tasks,
        options: {
          livereload: true,
        }
      }
    }
  });
  grunt.loadNpmTasks('grunt-sass-directory-import');
  grunt.loadNpmTasks('grunt-contrib-sass');
  grunt.loadNpmTasks('grunt-autoprefixer');
  grunt.loadNpmTasks('grunt-contrib-watch');

  grunt.registerTask('default', default_tasks);
};
