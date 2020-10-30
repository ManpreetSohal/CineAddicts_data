<?php

class Movie
{
    const QUERY = "INSERT INTO movies (wiki_id, title, release_date, runtime, poster_image_path, budget, box_office, synopsis) VALUES (:wiki_id, :title, :release_date, :runtime, :poster_image_path, :budget, :box_office, :synopsis)";
    public $title;
    public $wiki_id;
    public $release_date;
    public $runtime;
    public $poster_image_path;
    public $budget;
    public $box_office;
    public $synopsis;
}
