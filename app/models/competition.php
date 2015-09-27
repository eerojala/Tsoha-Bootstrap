<?php

class Competition extends BaseModel {

    public $id, $name, $location, $startsAt, $endsAt;

    public function __construct($attributes) {
        parent::__construct($attributes);
        $this->validators = array('validate_name', 'validate_location',
            'validate_startsAt', 'validate_endsAt');
    }

    public function save() {
        $query = DB::connection()->prepare('INSERT INTO Competition (competitionname,'
                . 'location, startsat, endsat) VALUES (:competitionname,'
                . ':location, :startsat, :endsat) RETURNING id');
        $query->execute($this->queryValues());
        $row = $query->fetch();
        $this->id = $row['id'];
    }
    
    private function queryValues() {
        $values = array();
        $values['competitionname'] = $this->name;
        $values['location'] = $this->location;
        $values['startsat'] = $this->startsAt;
        $values['endsat'] = $this->endsAt;
        return $values;
    }

    public function update() {
        $query = DB::connection()->prepare('UPDATE Competition '
                . 'SET competitionname = :competitionname, location = :location, '
                . 'startsat = :startsat, endsat= :endsat '
                . 'WHERE id = :id');
        $queryValues = $this->queryValues();
        $queryValues['id'] = $this->id;
        $query->execute($queryValues);
    }

    public static function all() {
        $query = DB::connection()->prepare('SELECT * FROM Competition');
        $query->execute();
        $rows = $query->fetchAll();
        $competitions = array();
        foreach ($rows as $row) {
            $competitions[] = new Competition(Competition::getAttributes($row));
        }
        return $competitions;
    }
    
    private static function getAttributes($row) {
        $attributes = array();
        $attributes['id'] = $row['id'];
        $attributes['name'] = $row['competitionname'];
        $attributes['location'] = $row['location'];
        $attributes['startsAt'] = date_format(new DateTime($row['startsat']), "d.m.Y G:i");
        $attributes['endsAt'] = date_format(new DateTime($row['endsat']), "d.m.Y G:i");
        return $attributes;
    }
    
    public static function find($id) {
        $query = DB::connection()->prepare('SELECT * FROM Competition WHERE id = :id LIMIT 1');
        $query->execute(array('id' => $id));
        $row = $query->fetch();

        if ($row) {
            return new Competition(Competition::getAttributes($row));
        }
        return null;
    }
    
    public static function delete($id) {
        $query = DB::connection()->prepare('DELETE FROM Competition WHERE id = :id');
        $query->execute(array('id' => $id));
    }

    public function validate_name() {
        $errors = array();
        if (!BaseModel::string_not_null_or_empty($this->name)) {
            $errors[] = 'Kilpailun nimi ei saa olla tyhjä';
        }
        if (!BaseModel::string_is_proper_length($this->name, 1, 100)) {
            $errors[] = 'Kilpailun nimen pituuden tulee olla välillä 1-100';
        }
        return $errors;
    }

    public function validate_location() {
        $errors = array();
        if (!BaseModel::string_not_null_or_empty($this->location)) {
            $errors[] = 'Järjestämispaikan nimi ei saa olla tyhjä';
        }
        if (!BaseModel::string_is_proper_length($this->location, 1, 100)) {
            $errors[] = 'Järjestämispaikan nimen pituuden tulee olla välillä 1-100';
        }
        return $errors;
    }

    public function validate_startsAt() {
        $errors = array();
        if (!BaseModel::dateTime_is_proper_format($this->startsAt)) {
            $errors[] = 'Alkamisajankohdan tulee olla muotoa d.m.yyyy mi:s';
        }
        return $errors;
    }

    public function validate_endsAt() {
        $errors = array();
        if (!BaseModel::dateTime_is_proper_format($this->endsAt)) {
            $errors[] = 'Päättymisajankohdan tulee olla muotoa d.m.yyyy mi:s';
        }
        return $errors;
    }

}
