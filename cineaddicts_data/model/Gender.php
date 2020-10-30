<?php

class Gender
{
    const QUERY = "INSERT INTO genders (wiki_id, gender) VALUES (:wiki_id, :gender)";
    public $wiki_id;
    public $gender;
}
