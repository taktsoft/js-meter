# Introduction
This is the initial release of the js meter, a profiling tool for HTML/Javascript in general and especially for jQuery.  
Further documentation will follow along the road. For now the code should be self-explanatory, but if any questions are left unanswered, don't hesitate to contact me at ju(at)taktsoft(dot)com.

# Files

## Files in document root:  

These files should be located on your webserver in your openly accessible htdocs directory.

* *db.php* - contains database settings, edit this for your local database settings
* *index_logging.html* - testpage to kickstart profiling your html
* *jsonlogger.php* - server component to receive the json-post-requests and save them to the database
* *report.php* - server component to generate the report of all profiling runs
* *util.php* - utility class
* *css/profiler/report.css* - css file to tweak minor layout things for the jQuery ui theme to match
* *javascript/FunMon2.js* - Pre-existing component to register functions for profiling, integrated with jquery-profile.js by John Resig in this plugin
* *javascript/jquery-1.3.2.min.js* - jQuery Javascript library, replace by your working copy
* *javascript/jquery-ui-1.7.2.custom.min.js* - jQuery UI library, used for styling and the accordion on the report
* *javascript/json2.js* - Javascript utility functions for JSON handling
* *javascript/jquery-profile.js* - modified version of John Resigs js profiling utility found here: http://ejohn.org/blog/deep-profiling-jquery-apps/

## Files _NOT_ in document root (e.g. Smarty templates):  

These should be in your Smarty templates directory, don't forget to edit report.php to reflect the location of your smarty templates

* *templates/reports.tpl* - Template for the whole report
* *templates/single_report.tpl* - Template for one single report, used by reports.tpl
