<?php

/* I'd normally use composer to include my dependencies, but since this is tiny and only has one page ... */

require_once( 'env.php' );
require_once( 'EmployeeController.php' );
require_once( 'Employee.php' );

/* I'd normally use a DB abstraction class, but again ... tiny, one page, etc */

$db = new \PDO( "mysql:host=" . getenv( 'DB_HOST' ) . ";dbname=" . getenv( 'DB_NAME' ) . ";charset=utf8", getenv( 'DB_USER' ), getenv( 'DB_PASS' ) );

/* check for search params */
$records_per_page = [ 50, 100, 200, 'All' ];
$page = ( isset( $_GET['page'] ) && is_numeric( $_GET['page'] ) ) ? intval( $_GET['page'] ) : 1;
$records = ( isset( $_GET['records'] ) && in_array( $_GET['records'], $records_per_page ) ) ? $_GET['records'] : $records_per_page[ 0 ];
$search = ( isset( $_GET['name'] ) && strlen( trim( $_GET['name'] ) ) > 0 ) ? trim( $_GET['name'] ) : NULL;

$controller = new EmployeeController;
$controller
    ->setSearchTerm( $search )
    ->loadAllEmployees()
    ->setPage( $page )
    ->setRecordsPerPage( $records );

?>

<!DOCTYPE html>
<html lang="en">
<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>Employees FTW!</title>

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="js/table-sortable.js"></script>

</head>
<body>

<div class="container">

	<div class="page-header">
		<h1>
            Employees!<br>
            <small>
                <?php echo number_format( $controller->getRecordCount() ); ?>
                Employee<?php if ( $controller->getRecordCount() != 1 ) { ?>s<?php } ?> Found
            </small>
        </h1>
	</div>

	<div class="row">

		<div class="col-md-3">

			<form class="well">
				<div class="form-group">
					<label for="name">Employee Name</label>
					<input class="form-control" id="name" name="name" value="<?php echo htmlspecialchars( $controller->getSearchTerm() ); ?>">
				</div>
				<div class="form-group">
					<label for="records">Employees per Page</label>
					<select class="form-control" id="records" name="records">
						<?php foreach ( $records_per_page as $number ) { ?>
                            <option value="<?php echo $number; ?>"<?php if ( $number == $records ) { ?> selected <?php } ?>>
                                <?php echo $number; ?>
                            </option>
                        <?php } ?>
					</select>
				</div>
				<button class="btn btn-primary">
                    <i class="fa fa-search"></i>
					Search
				</button>
                <a href="index.php" class="btn btn-danger">
                    <i class="fa fa-times"></i>
                    Clear
                </a>
			</form>

		</div>

		<div class="col-md-9">

			<p class="text-right">
				<?php if ( $controller->getPage() == 1 ) { ?>
					<i class="fa fa-chevron-left text-muted"></i>
				<?php } else { ?>
					<a href="?page=<?php echo $controller->getPrevPageNumber(); ?>&name=<?php echo $search; ?>&records=<?php echo $records; ?>"><i class="fa fa-chevron-left"></i></a>
				<?php } ?>
				<?php if ( $controller->getPage() == $controller->getPages() ) { ?>
					<i class="fa fa-chevron-right text-muted"></i>
				<?php } else { ?>
					<a href="?page=<?php echo $controller->getNextPageNumber(); ?>&name=<?php echo $search; ?>&records=<?php echo $records; ?>"><i class="fa fa-chevron-right"></i></a>
				<?php } ?>
				Page
				<?php echo $controller->getPage(); ?>
				of
				<?php echo $controller->getPages(); ?>
			</p>

			<table class="table table-bordered table-striped table-sortable">
				<thead>
					<tr>
						<th>Employee Name</th>
						<th>Boss Name</th>
						<th>Distance From CEO</th>
						<th>Number of Subordinates</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $controller->getEmployees() as $employee ) { ?>
						<tr>
							<td><?php echo $employee->getName(); ?></td>
							<td><?php echo $controller->getBossName( $employee ); ?></td>
							<td><?php echo number_format( $controller->getDistanceFromCEO( $employee ) ); ?></td>
                            <td><?php echo number_format( $controller->getSubordinateCount( $employee ) ); ?></td>
						</tr>
					<?php } ?>
				</tbody>
			</table>

		</div>

	</div>
</div>

</body>
</html>
