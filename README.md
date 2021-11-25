# WSPRd_web_interface
A simple web interface for WSPRd daemon statistics

# NOTE1: this code works only with the following wsprd daemon version modified by me! : https://github.com/IZ7BOJ/rtlsdr-wsprd/
The original wsprd version doesn't show date and time of received spots, that will be used for statistics. The daemon works whith rtlsdr dongles with direct sampling mode

NOTE2: the wsprd daemon shall be launched with a command similar to this:
sudo stdbuf -o L -e L <path_to_rtlsdr_wsprd>/rtlsdr_wsprd -c <your_call> -l <your_6_digits_locator> -d 2 -S -g <your_preferred_gain> -f <band_or_frequency> <path_and_log_filename> 2>&1 &

example, in my case: "sudo stdbuf -o L -e L ./rtlsdr_wsprd -c IZ7BOJ -l JN81fg -d 2 -S -g 29 -f 40m >/var/www/html/wspr/rtlsdrwspr_log.txt 2>&1 &"

You can also set crond for launching wsprd in different frequencies and different times of day
Main functions/characteristics of this web page:

- Show number of lines in log
- Show RX Load in spot/min calculated on last 20 spots
- Show system status table
- Show WSPRd configuration parameters in a separate table
- Show last spot list, grouped by call (pivot table)
- For every call, shows distance in Km and bearings in degrees with reference to receiver locator
- For every call, a complete list of received spots is available clicking on "show spot details"
- Possibility to make ascending or descendig sort in every column of heard stations
- Possibility to show custom info editing custom.php file

Prerequisites:
- php5
- web server (I tested the page on lighttpd installed into my raspberry pi3)

For installation just copy all files to the webistes folder in your WWW server directory. Make sure it supports PHP.
To configure, open config.php file with some text editor.
Enter the full path to your WSPRd log file

$logpath = "/some/path/<filename>.log";

Incorrect path will make the web page unable to work.
This was the only required step and now the software should work.

