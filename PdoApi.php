<?php 
/*
	* v 1.0
	* Реализованы стандартные самые распространенные операторы
	* Метод select;
		предназначен для вывода данных из БД. В виде аргумента принимает список колонок через запятую, которые
		необходимо вывести из таблицы. Также вместо списка колоное можно указать *, что является стандартным 
		синтаксисом запросов mysql.
	* Метод from
		является обязательным продолжением метода select. В виде аргумента следует передать название таблицы
		из которой производится вывод данных
	* Метод where
		указывает с какой строкой в таблице следует производить действия. В виде параметра используется массив.
		например: ...->where(['id'=>['>',1], 'name'=>['=','sergey']]); 
		'=' или '>' - указываем услове при котором производится вывод. В данном случае "Вывести строки из БД
		где id > 1 и name==sergey"
	* Метод insert
		предназначен для добавления данных в базу данных. В качестве параметра передается название таблицы.
	* Метод set
		продолжение метода insert. В качестве параметра передается массив такого вида ['id'=>1, 'name'=>"test", 'age'=>'55'];
	* Метод update
		в качестве параметра передается название таблицы. Предназначен для обновления данных в таблице. Использовать в связке
		с методом set.
		Использование метода без метода where в связке обновит все строки в таблице в колонках указанных в методе set. Рекуомендуется
		использовать в связке с методом where
	* Метод delete
		для удаления данных из таблицы. Без использования where в связке удаляет всю таблицу. Необходимо использовать с методом WHERE 
		для избежания потери данных
	* Метод limit
		Выводит $start-строка по счету и $count - количество строк после start
	* Метод start
		воспроизводит выполнение sql запроса.

	* Обращаться к данной библиотеки можно исключительно из модели через объект $this->db (далее цепочка методов)	
*/

class PdoApi
{
	private $sql;
	private $values=[];
	private $flag;
	private $pdo;

	public function __construct($pdo)
	{
		$this->pdo=$pdo;
	}

	public function select($cols)//пример $this->db->select('*')->from('test')->where(['id'=>['>',1]])->orderby('id', 'DESC');
	{		
		$this->sql="SELECT $cols ";
		$this->flag='SELECT';
		return $this;
	}
	public function from($table)
	{		
		$this->sql.="FROM $table ";
		return $this;
	}
	public function where($values)
	{
		$this->sql.='WHERE TRUE ';
		foreach($values as $key=>$val)
		{		
			$this->sql.="AND $key $val[0] :$key ";
		}
		foreach($values as $key=>$val)
		{
			$this->values[$key]=$val[1];
		}		
		return $this;
	}
	public function insert($table)//пример $this->db->insert('test')->set(['name'=>'NM','age'=>'56']);
	{
		$this->flag='INSERT';
		$this->sql="INSERT INTO $table SET ";
		return $this;
	}
	public function set($values)
	{
		foreach($values as $key=>$val)
		{
			$this->sql.="$key=:$key,";
		}
		$this->values=$values;
		$this->sql=rtrim($this->sql,',');
		$this->sql.=' ';
		return $this;
	}
	public function update($table)//пример $this->db->update('test')->set(['name'=>'proverka'])->where(['id'=>[3,'=']]);
	{
		$this->flag='UPDATE';
		$this->sql="UPDATE $table SET ";
		return $this;
	}
	public function delete($table)
	{
		$this->flag='DELETE';
		$this->sql="DELETE FROM $table ";
		return $this;
	}
	public function limit($start, $count)
	{		
		$this->sql.="LIMIT $start,$count ";
		return $this;
	}
	public function orderby($col, $action="ASC")
	{
		$this->sql.="ORDER BY $col $action ";
		return $this;
	}
	public function start()
	{			
		try
		{
			$s=$this->pdo->prepare($this->sql);
			$s->execute($this->values);
		}
		catch(PDOException $e){return ['error'=>true, 'mess'=>'Ошибка базы данных'];}

		if($this->flag=='SELECT'){return ['error'=>false, 'data'=>$s->fetchAll()];}
		if($this->flag=='INSERT' || $this->flag=='DELETE' || $this->flag=='UPDATE')return ['error'=>false, 'mess'=>'Данные обработаны'];			
	}
}