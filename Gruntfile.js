module.exports = function(grunt) {

    grunt.initConfig({
        bower_concat: {
            all: {
                dest: 'public_html/js/bower-combined.js',
                cssDest: 'public_html/css/bower-combined.css',
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
        concat: {
            options: {
                // define a string to put between each file in the concatenated output
                separator: ';'
            },
            dist: {
                // the files to concatenate
                src: ['public_html/js/*.js'],
                // the location of the resulting JS file
                dest: 'public_html/build/aggregated.js'
            }
        },
        concat_css: {
            options: {
                // Task-specific options go here.
            },
            all: {
                src: ['public_html/css/*.css'],
                dest: 'public_html/build/aggregated.css'
            },
        },
        uglify: {
            options: {
                //banner: '/*! aggregated <%= grunt.template.today("dd-mm-yyyy") %> */\n'
            },
            dist: {
                files: {
                    'public_html/build/aggregated.min.js': ['<%= concat.dist.dest %>']
                }
            }
        },
        cssmin: {
            add_banner: {
                options: {
                   // banner: '/* My minified css file */'
                },
                files: {
                    'public_html/build/aggregated.min.css': ['<%= concat_css.all.dest %>']
                }
            }
        },
        jshint: {
            files: ['Gruntfile.js', 'public_html/js/*.js', '!<%= bower_concat.all.dest %>'],
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
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-bower-concat');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-concat-css');

    grunt.registerTask('test', ['jshint']);

    grunt.registerTask('default', ['jshint', 'bower_concat', 'concat', 'concat_css', 'cssmin', 'uglify']);

};