module.exports = function(grunt) {

    grunt.initConfig({
        bower_concat: {
            all: {
                dest: 'public_html/build/_bower.js',
                cssDest: 'public_html/build/_bower.css',
                exclude: [
                    'modernizr'
                ],
                dependencies: {
                    'bootstrap': 'jquery'
                },
                bowerOptions: {
                    relative: false
                }
            }
        },
        uglify: {
            options: {
                banner: '/*! aggregated <%= grunt.template.today("dd-mm-yyyy") %> */\n'
            },
            dist: {
                files: {
                    'public_html/build/aggregated.min.js': ['<%= bower_concat.all.dest %>']
                }
            }
        },
        cssmin: {
            add_banner: {
                options: {
                    banner: '/* My minified css file */'
                },
                files: {
                    'public_html/build/aggregated.css': ['public_html/build/_bower.css']
                }
            }
        },
        jshint: {
            files: ['Gruntfile.js', 'src/**/*.js', 'test/**/*.js'],
            options: {
                // options here to override JSHint defaults
                globals: {
                    jQuery: true,
                    console: true,
                    module: true,
                    document: true
                }
            }
        },
        watch: {
            files: ['<%= jshint.files %>'],
            tasks: ['jshint']
        }
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-qunit');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-bower-concat');

    grunt.registerTask('test', ['jshint']);

    grunt.registerTask('default', ['jshint', 'bower_concat', 'cssmin', 'uglify']);

};