<?php

class EmployeeController {

	private $records_per_page;
	private $page = 1;
	private $search_term;

	/** @var Employee[] $employees */
	private $employees = [];

	private $filtered_employee_ids = [];
	private $subordinates = [];
	private $subordinate_counts_have_been_loaded = FALSE;
	private $ceo_id;

	/**
	 * @return Employee[]
	 */
	public function getEmployees()
	{
		return array_slice( $this->getFilteredEmployees(), $this->getOffset(), $this->getRecordsPerPage() );
	}

	/**
	 * @return Employee[]
	 */
	private function getFilteredEmployees()
	{
		if ( count( $this->filtered_employee_ids ) == count( $this->employees ) )
		{
			return $this->employees;
		}

		$employees = [];

		foreach ( $this->filtered_employee_ids as $id )
		{
			$employees[ $id ] = $this->employees[ $id ];
		}

		return $employees;
	}

	/**
	 * @param Employee[] $employees
	 *
	 * @return EmployeeController
	 */
	public function setEmployees( $employees )
	{
		$this->employees = $employees;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getEmployeeCount()
	{
		return count( $this->filtered_employee_ids );
	}

	/**
	 * @return int
	 */
	public function getRecordsPerPage()
	{
		return ( $this->records_per_page === NULL ) ? $this->getRecordCount() : $this->records_per_page;
	}

	/**
	 * @param int $records_per_page
	 *
	 * @return EmployeeController
	 */
	public function setRecordsPerPage( $records_per_page )
	{
		if ( is_numeric( $records_per_page ) && $records_per_page > 0 )
		{
			$this->records_per_page = intval( $records_per_page );
		}

		return $this;
	}

	/**
	 * @return int
	 */
	public function getRecordCount()
	{
		return count( $this->filtered_employee_ids );
	}

	/**
	 * @return int
	 */
	public function getPage()
	{
		return ( $this->page > $this->getPages() ) ? $this->getPages() : $this->page;
	}

	/**
	 * @return int
	 */
	public function getPrevPageNumber()
	{
		return ( $this->getPage() == 1 ) ? 1 : $this->getPage() - 1;
	}

	/**
	 * @return int
	 */
	public function getNextPageNumber()
	{
		return ( $this->getPage() == $this->getPages() ) ? $this->getPages() : $this->getPage() + 1;
	}

	/**
	 * @param int $page
	 *
	 * @return EmployeeController
	 */
	public function setPage( $page )
	{
		$this->page = $page;

		return $this;
	}

	/**
	 * @return float
	 */
	public function getPages()
	{
		return ceil( $this->getEmployeeCount() / $this->getRecordsPerPage() );
	}

	public function getOffset()
	{
		return ( $this->getPage() - 1 ) * $this->getRecordsPerPage();
	}

	/**
	 * @return mixed
	 */
	public function getSearchTerm()
	{
		return ( $this->search_term === NULL ) ? '' : $this->search_term;
	}

	/**
	 * @param mixed $search_term
	 *
	 * @return EmployeeController
	 */
	public function setSearchTerm( $search_term )
	{
		$this->search_term = ( strlen( $search_term ) > 0 ) ? $search_term : NULL;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function loadAllEmployees()
	{
		/** @var \PDO $db */
		global $db;

		$this->employees = [];
		$this->subordinates = [];
		$this->filtered_employee_ids = [];

		$sql = "
			SELECT
				*
			FROM
				employees
			ORDER BY 	
				id ASC";
		$stmt = $db->prepare( $sql );
		$stmt->execute();

		while ( $row = $stmt->fetchObject() )
		{
			/* add employee to the master employees array */

			$employee = new Employee;
			$employee->loadFromObject( $row );
			$this->employees[ $employee->getId() ] = $employee;

			/* create filtered employee id array based on search tearm being present */

			if ( $this->search_term !== NULL )
			{
				if ( strpos( strtoupper( $employee->getName() ), strtoupper( $this->search_term ) ) !== FALSE )
				{
					$this->filtered_employee_ids[] = $employee->getId();
				}
			}
			else
			{
				$this->filtered_employee_ids[] = $employee->getId();
			}

			/* create a tree to calculate subordinates */

			if ( $employee->hasBoss() )
			{
				if ( ! array_key_exists( $employee->getBossId(), $this->subordinates ) )
				{
					$this->subordinates[ $employee->getBossId() ] = [
						'count' => NULL,
						'subs' => []
					];
				}

				$this->subordinates[ $employee->getBossId() ]['subs'][] = $employee->getId();
			}
			else
			{
				$this->ceo_id = $employee->getId();
			}
		}

		return $this;
	}

	/**
	 * I could store the boss name in the Employee object,
	 * but since I have all the objects in my collection, this is just as easy.
	 *
	 * @param Employee $employee
	 *
	 * @return string
	 */
	public function getBossName( $employee )
	{
		return ( array_key_exists( $employee->getBossId(), $this->employees ) ) ? $this->employees[ $employee->getBossId() ]->getName() : '';
	}

	/**
	 * This method just climbs up the tree until it hits the boss
	 *
	 * @param Employee $employee
	 *
	 * @return int
	 */
	public function getDistanceFromCEO( $employee )
	{
		$distance = 0;
		$temp_boss_id = $employee->getBossId();

		while ( $employee->hasBoss() )
		{
			if ( array_key_exists( $temp_boss_id, $this->employees ) )
			{
				$distance ++;

				if ( $this->employees[ $temp_boss_id ]->hasBoss() )
				{
					$temp_boss_id = $this->employees[ $temp_boss_id ]->getBossId();
				}
				else
				{
					break;
				}
			}
			else
			{
				break;
			}
		}

		return $distance;
	}

	/**
	 * If the loadSubordinateCount() method hasn't been run, it will do that once
	 * and then used cached values from then on
	 *
	 * @param Employee $employee
	 *
	 * @return int
	 */
	public function getSubordinateCount( $employee )
	{
		if ( array_key_exists( $employee->getId(), $this->subordinates ) )
		{
			if ( ! $this->subordinate_counts_have_been_loaded )
			{
				$this->loadSubordinateCount( $this->ceo_id );
				$this->subordinate_counts_have_been_loaded = TRUE;
			}

			return $this->subordinates[ $employee->getId() ]['count'];
		}

		return 0;
	}

	/**
	 * Recursive function to cache all subordinate counts
	 * so it doesn't run each time the getSubordinateCount() method is called
	 *
	 * @param int $employee_id
	 * @param array $upstream_ids
	 */
	private function loadSubordinateCount( $employee_id, $upstream_ids = [] )
	{
		if ( array_key_exists( $employee_id, $this->subordinates ) )
		{
			$count = count( $this->subordinates[ $employee_id ]['subs'] );
			$this->subordinates[ $employee_id ]['count'] += $count;

			foreach ( $upstream_ids as $id )
			{
				$this->subordinates[ $id ]['count'] += $count;
			}

			$upstream_ids[] = $employee_id;

			foreach ( $this->subordinates[ $employee_id ]['subs'] as $sub_id )
			{
				$this->loadSubordinateCount( $sub_id, $upstream_ids );
			}
		}
	}
}