phpwtf
======

What is it?
-----------

You know Pdepend, PHPMD? well it's a WTF counter for PHP, useful for codereviews
The concept is simple, when you review some code, if you think :
 - WTF!? 
 - Why? Why? Why!!! 

You simply add a @wtf_start and @wtf_stop as a comment before and after the code that needs ~~explanations or~~ fixing.
PHPWTF will parse the code and report the wtfs it finds.
Ideally we could even make it usable by Jenkins with a nice graph and code exploration :)


Installation
------------

/!\ THIS TOOL IS TO BE INSTALLED WITH COMPOSER : <https://github.com/composer/composer>

<strong>if you already know how to use composer you can skip this part and go to Usage section.</strong>

Basically you need to:

1.	Create a directory where you want to run your composer install, ie : phpwtf_test
2.	Download the composer.phar into your phpwtf_test folder
3.	Create a composer.json file in the phpwtf_test folder like : <br/>
	<pre><code>{
		"name": "nameOfYourProject",
		"minimum-stability": "dev",
		"require": {
			"php": ">=5.3.3",
			"pem-fr/phpwtf": "dev-master"
		}
	}</code></pre>
4.	Then, still from the phpwtf_test folder, run this command line : php composer.phar install
5. Verify that in the root folder (in our example phpwtf_test) you have a composer.lock file and a vendor directory.
6. Congratulations you have installed phpwtf through composer :)


Usage
-----

To run the example, you just need to :

1. Go to where you installed phpwtf, in the root folder then type in your console :<br/>
	<pre><code>php vendor/bin/phpwtf review --paths="./vendor/phpwtf/phpwtf/examples/*.php,./vendor/phpwtf/phpwtf/examples/*.js,./vendor/phpwtf/phpwtf/examples/*.html" -r --format=html --output-path="../reports/"</code></pre>
2. Now you should see in your root folder, a reports directory with the html reports

Of course phptwtf script supports help command

Args can be written like that :<br/>
	<pre><code>php vendor/bin/phpwtf.php review --paths="path/*.php,path/*.js" --format=html --output-path="path/to/reports/" -r -b</code></pre>

This list of options is non-exhaustive and can change anytime. You can use the help or list command to have more up to date information at any time. Also note that since they are all options, they can be put in *any* order.
 
    --input-path    -p      Paths to scan for sources, 
                                ie: "/some/path/*.php,/some/other/path/*.js"
                                Default to "/". Becareful, relative path are resolved 
                                from either vendor folder or root folder if you 
                                have not installed phpwtf with composer.

    --recursive     -r      If set, the paths will be scanned recursively. Defaults to *FALSE*

    --format        -f      by default xml, can be set to html, html+stats, xml+stats
                                the xml is a simple xml with files, and errors
                                the html is a set of pages per file, with the errors
                                reported + stats stats will just ouput statistics
                                about the nb of WTF, etc
                                finally you can combine output format by using "+"

    --output-path   -o      defaults to ./reports/
                                the directory where you want your reports to be written
	
    --skip-error    -s      if set, errors will not stop the parsing.

    --bench         -b      To display timings.


What more?
----------

Accepting contributions (jenkins integration, code exploration, graphs, better parsing...) :)

The Logo and favicon are courtesy of David Smith and under MIT Licence of the phpwtf project.
