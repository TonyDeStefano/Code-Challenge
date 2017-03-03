<?php

class Employee {

	private $id;
	private $name;
	private $boss_id;

	/** @var Employee $boss */
	private $boss;

	public function __construct( $id = NULL )
	{
		$this
			->setId( $id )
			->read();
	}

	public function read()
	{

	}

	/**
	 * @param \stdClass $row
	 */
	public function loadFromObject( $row )
	{
		$this
			->setId( $row->id )
			->setName( $row->name )
			->setBossId( $row->bossId );
	}

	/**
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param mixed $id
	 *
	 * @return Employee
	 */
	public function setId( $id )
	{
		$this->id = ( is_numeric( $id ) ) ? intval( $id ) : NULL;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return ( $this->name === NULL ) ? '' : $this->name;
	}

	/**
	 * @param mixed $name
	 *
	 * @return Employee
	 */
	public function setName( $name )
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getBossId()
	{
		return $this->boss_id;
	}

	/**
	 * @param mixed $boss_id
	 *
	 * @return Employee
	 */
	public function setBossId( $boss_id )
	{
		$this->boss_id = ( is_numeric( $boss_id ) ) ? intval( $boss_id ) : NULL;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasBoss()
	{
		return ( $this->boss_id !== NULL && $this->id != $this->boss_id );
	}
}