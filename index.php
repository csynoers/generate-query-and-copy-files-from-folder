<?php

    class Generate
    {
        public $dir            ;
        public $dirTarget      ;
        public $table          ;
        public $tableFields    ;
        
        function __construct($config=NULL) {
            if ( $config ) {
                if ( ! ($config['dir'] && $config['dirTarget'] && $config['table'] && $config['tableFields']) ) {
                    if ( ! $config['dir'] ) {
                        echo '! dir is required<br>';
                    }
                    if ( ! $config['dirTarget'] ) {
                        echo '! dirTarget is required<br>';
                    }
                    if ( ! $config['table'] ) {
                        echo '! table is required<br>';
                    }
                    if ( ! $config['tableFields'] ) {
                        echo '! tableFields is required<br>';
                    }
                    die();
                } else {
                    $this->dir         = $config['dir'];
                    $this->dirTarget   = $config['dirTarget'];
                    $this->table       = $config['table'];
                    $this->tableFields = $config['tableFields'];
                }
            } else {
                echo '
                    config required like this:<br>
                    $data = new Generate([
                        "dir"        => string,
                        "dirTarget"  => string,
                        "table"      => string,
                        "tableFields"=> array
                    ]);
                ';
                die();
            }
        }
        
        function queryBuilder($data) {
            $query = "";
            $target = [];
            $values = [];

            foreach ($this->tableFields as $key => $value) {
                $target[] = "`{$value}`";
            }
            $target = implode(',',$target);
            foreach ($data as $key => $value) {
                $valuesSatu = [];
                foreach ($this->tableFields as $keySatu => $valueSatu) {
                    $valuesSatu[] = "'{$value[$valueSatu]}'"; 
                }
                $values[] = '(' . implode(',',$valuesSatu) . ')';
            }
            $values = implode(',',$values);
            $query .= "INSERT INTO `{$this->table}`({$target}) VALUES {$values};";
            
            return $query;
        }
    
        public function dirToArray($dir) {
            $result = array();
         
            $cdir = scandir($dir);
            foreach ($cdir as $key => $value)
            {
               if (!in_array($value,array(".","..")))
               {
                  if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
                  {
                     $result[$value] = $this->dirToArray($dir . DIRECTORY_SEPARATOR . $value);
                  }
                  else
                  {
                     $result[] = $value;
                  }
               }
            }
           
            return $result;
        }
    
        function copyAndRenameFile($file,$dinamycDirFile) {
            $info = pathinfo($file);
            $file_name =  basename($file,'.'.$info['extension']);
            $file_name =  basename($file,'.'.$info['extension']);
            
            $file = $file;
            $newfile = strtolower( str_replace(' ',"-",$file_name) ).".{$info['extension']}";

            $targetFile = $this->dir . $dinamycDirFile . DIRECTORY_SEPARATOR . $file;
            $targerFileToDir = $this->dirTarget . DIRECTORY_SEPARATOR .$newfile;

            if (!copy($targetFile, $targerFileToDir)) {
                $targerFileToDir = "FAILED";
            }
            // str_replace(' ',"-",$file_name)
            return [
                'title' => $file_name,
                'file_name' => $newfile
            ];
        }
    
        function generateQueryFromArrayDir() {
            $result = [];
            foreach ($this->dirToArray($this->dir) as $key => $value) {
                if ( is_array($value) ) {
                    foreach ($value as $keySatu => $valueSatu) {
                        if ( is_array($valueSatu) ) {
                            foreach ($valueSatu as $keyDua => $valueDua) {
                                if ( $this->tableFields ) {
                                    $dinamycDirFile = DIRECTORY_SEPARATOR . $key . DIRECTORY_SEPARATOR . $keySatu;
                                    $copyAndRenameFile = $this->copyAndRenameFile($valueDua,$dinamycDirFile);
                                    // $generateNameWithoutStrip   = 
                                    $result[] = [
                                        $this->tableFields[0] => $key,
                                        $this->tableFields[1] => $keySatu,
                                        $this->tableFields[2] => $copyAndRenameFile['title'],
                                        $this->tableFields[3] => $copyAndRenameFile['file_name'],
                                    ];
                                }
                            }
    
                        }
                    }
    
                }
            }
            return $this->queryBuilder($result);
        }
        
    }
    
    /* config Example :
        $data = new Generate([
            'dir'        => '.data',
            'dirTarget'  => './data-result';
            'table'      => "galeri_foto_porto";
            'tableFields'=> ["id_portofolio","stat","nama","image"];
        ]);
    */
    $data = new Generate([
        'dir'        => './data',
        'dirTarget'  => './data-result',
        'table'      => "galeri_foto_porto",
        'tableFields'=> ["id_portofolio","stat","nama","image"],
    ]);

    echo '<pre>';
    print_r( $data->generateQueryFromArrayDir() );
    echo '<pre>';