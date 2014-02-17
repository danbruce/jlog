#JLog Logging Framework

##Summary
JLog is a simple logging framework written for PHP that uses the JSON format by default. This format
makes for more machine-readable logs to be parsed by external tools.

JLog is capable of writing any variable type that is serializable to JSON (scalar, array, or
object).

The minimum supported PHP version is 5.4. If you need support for earlier versions of PHP please
contact me.

##Supported Stores
JLog currently supports writing logged events to the following locations:
* Standard output (`echo`/`print`)
* Standard error (`error_log()`)
* Log folder output (one file per transaction)
* Email
* MySQL database

##Using JLog
JLog provides an autoload method for loading in the namespaced classes. Add:
    
    require_once 'JLog/autoload.php';
    JLog::init(YOUR SETTINGS HERE);

to your code base (in the initialization) to ensure the JLog classes are accessible.

##Future features
The next set of features I hope to add:
* Optional short global function scope.
* Optional level squelching (ignoring logging messages below a specific logging level).
* Message buffering
* Additional stores
  * File store (one log file for all transactions)
  * Socket store (echo'ing logged elements to a socket)
  * HTTP store (posting logged messages to a REST-like API endpoint)
  * PDO generic database file store

##Development
A `Vagrantfile` and `install.sh` are provided for installing and setting up a development
environment.

###Installation instructions
1. Install vagrant (http://www.vagrantup.com/) using the appropriate instructions for your platform.
2. Clone this repository.
3. `cd jlog`
4. `vagrant up`
5. `vagrant ssh`
6. `sudo /vagrant/install.sh`

If installation is successful you should now have a working development environment.

###Running the unit tests
1. `cd /vagrant`
2. `phpunit`

The unit test coverage report can be opened in your web browser at `JLogTests/report/index.html`.

###Generating doxygen documentation
1. `cd /vagrant`
2. `doxygen doxygen.conf`

The documentation can be viewed in your browser at `html/index.html`.