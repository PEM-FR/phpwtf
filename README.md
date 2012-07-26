phpwtf
======

You know Pdepend, PHPMD? well it's a WTF counter for PHP, usefull for codereviews
The concept is simple, when you review some code, if you think :
 - What The Fuck? 
 - What The Fuck is that shit? 
 - Who The Fuck made this? 

You simply add a // @WTF as a comment before the code that needs fixing.
PHPWTF will parse the code and note every @WTF it finds, and report them with file and line number.
Ideally we could even make it usable by Jenkins with a nice graph and code exploration :)

Accepting contributions :)