# php-mortgage-calculator
This is a basic PHP web page that offers a few different options for calculating mortgage rates. This is basically me trying to understand how mortgage payment amounts are calculated. Once I figured out the math, I decided to make a nice little web application so that I could make use of my efforts every once in a while. I also tried to make the calculations generic enough that they could be used with other loan types as well.

## Setup
* Clone this repository to your local machine.
* Install the composer libraries `composer install`.
* Depending on how you deploy this project to your HTTP server, you may need to redirect all public traffic to public/index.php so that routing works correctly. This can be done using something like an .htaccess (Apache) or web.config (IIS) file.
* To run the application locally, using the built-in php HTTP server, use the following terminal command `composer start`.
* To run the unit tests, use the following terminal command `composer test`.