<?php 

class WikiAlbum extends WikiBase {
    private $filename = null;

    private $albumProperties = [
        'P21' => ['title' => 'gender', 'multiple_values' => false]
    ];

    public function __construct($filename){
        $this->filename = $filename;
        parent::__construct($this->albumProperties);
    }
}

?>