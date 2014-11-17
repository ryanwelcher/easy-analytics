module.exports = function(grunt) {

    grunt.initConfig({

        pkg: grunt.file.readJSON('package.json'),

        makepot: {
            target: {
                options: {
                    domainPath: 'languages',
                    cwd : './',
                    type: 'wp-plugin'
                }
            }
        },
    });

    grunt.loadNpmTasks('grunt-wp-i18n');

    grunt.registerTask('default', [
        'makepot'
    ]);
}