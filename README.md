# Code-Challenge

In order for this to work, you just need to input your DB info into _env.php and then rename it to env.php (which is ignored from the project for security purposes)

## Files Included

+ **index.php** - this is the main html file
+ **env.php** - this stores DB connect variabls
+ **Employee.php** - this is the Model for the employee object
+ **EmployeeController.php** - this is the Controller for the project
+ **js/table-sortable.js** - this is a lightweight table sorting script that I wrote a few months back

## Screenshots

+ [Default](../blob/master/screenshots/1.%20default.png)
+ [Search by Name](../blob/master/screenshots/2.%20search-by-name.png)
+ [Sort by Boss Name](../blob/master/screenshots/3.%20sort-by-boss-name.png)
+ [Paging Example](../blob/master/screenshots/4.%20paging-and-records-per-page.png)
+ [Another Sorting Example](../blob/master/screenshots/5.%20sort-by-number-of-subordinates.png)

## Notes

I chose to do this by only querying the datbase one time for all employees.
If the database was huge and/or the employee table had more than a few columns, 
that may not have been the most efficient way. But I like to limit my db
calls as much as possible. And PHP can easily handle 10,000 pieces of data.

I also would normally use composer to include my dependencies and use a DB
abstraction class like Eloquent or something home-grown. But since this
project was tiny, I thought that might be overkill.