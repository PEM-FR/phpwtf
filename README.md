phpwtf
======

You know Pdepend, PHPMD? well it's a WTF counter for PHP, usefull for codereviews
The concept is simple, when you review some code, if you think :
 - What The Fuck? 
 - What The Fuck is that shit? 
 - Who The Fuck made this? 

You simply add a @wtf_start and @wtf_stop as a comment before and after the code that needs fixing.
PHPWTF will parse the code and report the wtfs it finds.
Ideally we could even make it usable by Jenkins with a nice graph and code exploration :)

To run the example, you just need to go to where your the script is then type in your console :
php phpwtf.php --paths="./examples/*.php,./examples/*.js,./examples/*.html" -r --format=html --output-path="./reports/"

of course phptwtf script supports help command

// Args can be written like that :
// php phpwtf.php review --paths="path/*.php,path/*.js" --format=html -r -b

// This list of options is non-exhaustive and can change anytime. You can use
// the help or list command to have more up to date information at any time
// Also note that since they are all options, they can be put in *any* order.
 
// --paths          parameters given to a path function

// --recursive -r   if not set then false

// --format         by default xml, can be set to html, html+stats, xml+stats
//                  the xml is a simple xml with files, and errors
//                  the html is a set of pages per file, with the errors
//                  reported + stats stats will just ouput statistics
//                  about the nb of WTF, etc
//                  finally you can combine output format by using +

// --output-path    defaults to ./reports/
//                  the directory where you want your reports to be written

// --bench    -b    To display timings.


Accepting contributions :)

The Logo and favicon are courtesy of David Smith and under MIT Licence of the phpwtf project.