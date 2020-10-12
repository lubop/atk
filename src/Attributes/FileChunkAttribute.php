<?php
namespace Sintattica\Atk\Attributes;

use Sintattica\Atk\Attributes\Attribute;
use Sintattica\Atk\Core\Config;
use Sintattica\Atk\Core\Tools;
use Sintattica\Atk\Ui\Page;
use Sintattica\Atk\Utils\StringParser;
use Nette\Utils;


class FileChunkAttribute extends Attribute
{
    public const AF_MULTIUPLOAD = 67108864;
    public const AF_SIGLE_FILE = 134217728;
    public const AF_SHOW_FILENAME_IN_DISPLAY = 268435456;
    public const AF_DELETE_FILE = 536870912;


    private $cell_counter = 0;
    /*
     * Directory with images
     */
    public $m_dir = '';
    public $m_url = '';

    /**
     * @var array
     */
    private $record_params;

    /**
     * Constructor.
     *
     * @param string $name Name of the attribute
     * @param int $flags Flags for this attribute
     * @param array|string $dir Can be a string with the Directory with images/files or an array with a Directory and a Display Url
     */
    public function __construct($name, $flags = 0, $dir)
    {
        $flags |= self::AF_CASCADE_DELETE;
        parent::__construct($name, $flags);

        $this->setDir($dir);
        // $this->setStorageType(self::POSTSTORE | self::ADDTOQUERY);
    }

    /**
     * Sets the directory into which uploaded files are saved.  (See setAutonumbering() and setFilenameTemplate()
     * for some other ways of manipulating the names of uploaded files.).
     *
     * @param mixed $dir string with directory path or array with directory path and display url (see constructor)
     * @return FileChunkAttribute
     */
    public function setDir($dir)
    {
        if (is_array($dir)) {
            $this->m_dir = $this->AddSlash($dir[0]);
            $this->m_url = $this->AddSlash($dir[1]);
        } else {
            $this->m_dir = $this->AddSlash($dir);
            $this->m_url = $this->AddSlash($dir);
        }

        if (!is_dir($this->m_dir))
        {
            Utils\FileSystem::createDir($this->m_dir);
        }


        return $this;
    }

    /**
     * returns a string with a / on the end.
     *
     * @param string $dir_url String with the url/dir
     *
     * @return string with a / on the end
     */
    public function AddSlash($dir_url)
    {
        if (substr($dir_url, -1) !== '/') {
            $dir_url .= '/';
        }

        return $dir_url;
    }


    public function setRecordParams($rp)
    {
        $this->record_params = $rp;
    }




    public function display($record, $mode="")
    {
        global $custom_config;

        //$relpath = $custom_config['config_relpath'];
        $relpath = $this->m_url;
        $field=$this->fieldName();
        $owner=$this->getOwnerInstance();
        $table=$owner->getTable();
        ++$this->cell_counter;

        $e1=explode("=",$record['atkprimkey']);
        $e2=str_replace("'","",$e1[1]);
        $primary_id = $e2;

        $id="fu_".$this->cell_counter."_".$primary_id."_".$field;

        $original_display = parent::display($record, $mode);
        $primary = $record['atkprimkey'];

        $node = $this->m_ownerInstance;


        if (!empty($original_display))
        {
            $original_display = ltrim($original_display, ';');
            $ex_file = explode(";", $original_display);

            if (is_array($ex_file))
            {
                if (count($ex_file) > 1)
                {
                    $ret = '<ul class="m-0 p-0">';
                    $pocet = count($ex_file);
                    for ($i = 0; $i < $pocet; $i++)
                    {
                        if (trim($ex_file[$i]) == "") continue;
                        $f_i = trim($ex_file[$i]);
                        $ret .= '<li style="list-style: none;">' . $this->_show_file($f_i) . '</li>';

                    }
                    $ret .= '</ul>';
                } else
                {
                    $ret = $this->_show_file($original_display);
                }
            } else
            {
                if (!empty($original_display))
                {
                    $ret = $this->_show_file($original_display);
                } else
                {
                    $ret = '';
                }
            }
        } else {
            $ret = '';
        }

        return html_entity_decode($ret);


    }






    public function _show_file($file_name)
    {
        $file_with_path = $this->m_dir . $file_name;
        $file_with_uri = $this->m_url . $file_name;
        $x_arr = explode('.', $file_name);
        $ext = strtolower($x_arr[count($x_arr)-1]);
        $file_crypted = wn_encrypt($file_with_path);

        $ret = null;

        $fname_display = null;
        if ($this->hasFlag(self::AF_SHOW_FILENAME_IN_DISPLAY)) {
            $fname_display = $file_name;
        }

        switch ($ext)
        {
            case 'pdf':
                {
                    $ret .= '<a class="text-nowrap" href="javascript:;" onclick="PopupCenter(\''.Config::getGlobal('base_uri').'services/download.php?index=' . base64_encode($file_crypted) . '\', \'Náhľad\', 800,600)"><i class="icon-file-download2 mr-1"></i> ' . $fname_display.'</a>';
                    break;
                }
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                {
                    $ret .= '<div data-imagepopup><a class="text-nowrap" href="'.Config::getGlobal('base_uri').'services/download.php?index=' . base64_encode($file_crypted) . '"><i class="icon-file-download2 mr-1"></i> ' . $fname_display.'</a></div>';
                    // $ret .= '<div data-imagepopup><a class="text-nowrap" href="'. $file_with_uri. '"><i class="icon-search4 mr-1"></i>' . trim($file_name) . '</a></div>';
                    break;
                }


            default:
                {
                    $ret .= '<a class="text-nowrap" target="_blank" href="'.Config::getGlobal('base_uri').'services/download.php?index=' . base64_encode($file_crypted) . '"><i class="icon-file-download2 mr-1"></i> ' . $fname_display.'</a>';
                    break;
                }
        }


        return $ret;



    }

    public function edit($record = '', $fieldprefix = '', $mode = '')
    {

        $this->getOwnerInstance()->getPage()->register_script(Config::getGlobal('base_uri').'services/resumable/resumable.js');
        $this->getOwnerInstance()->getPage()->register_script(Config::getGlobal('base_uri').'services/resumable/extra_func.js');

        if ($this->hasFlag(self::AF_SIGLE_FILE))
        {
            $pocet_suborov = 1;
        } else {
            $pocet_suborov = 99;
        }

        $value = null;
        if (isset($record[$this->fieldName()])) $value = $record[$this->fieldName()];

        $name = $fieldprefix.$this->fieldName();
        $but_name = "but_".$fieldprefix.$this->fieldName();
        $win_name = "win_".$fieldprefix.$this->fieldName();

        $uid = uniqid();

        $keyid = 'null';
        if (!empty($record[$this->record_params['keyname']])) $keyid = $record[$this->record_params['keyname']];

        if ($this->hasFlag(self::AF_MULTIUPLOAD)) $pocet_uploadov = 10;
        else $pocet_uploadov = 1;

        $in = '<script type="text/javascript">
        //<![CDATA[   
        
        var r'.$uid.' = new Resumable({
            target:"'.Config::getGlobal('base_uri').'services/resumable/do-upload/",
            useResumableChunkNumberAsRewrite: true,
            query: {path:"'.base64_encode($this->m_dir).'"},
            simultaneousUploads :1,
            maxFiles: '.$pocet_uploadov.',
            generateUniqueIdentifier:genUname
        });

        chunkuploader_reload_box("'.$pocet_suborov.'","'.$but_name.'","'.$name.'","box_filelist_'.$uid.'", "'.$this->record_params['db'].'", "'.$this->record_params['table'].'", "'.$this->record_params['keyname'].'", '.$keyid.', "'.base64_encode($this->m_dir).'");

        r'.$uid.'.assignBrowse(document.getElementById("'.$but_name.'"));
        r'.$uid.'.on("fileSuccess", function(file){
            
            $("#bu_status'.$uid.'").html(\'<b style="color:green">Súbor bol úspešne nahraný sa server.... </b>\');
            
            setTimeout(function()
            {
                $("#bu_status'.$uid.'").html("");
            },2000);
            
            if ($(\'[name="atksaveandnext"]\') != null) $(\'[name="atksaveandnext"]\').show();            
            if ($(\'[name="atksaveandclose"]\') != null) $(\'[name="atksaveandclose"]\').show();
            if ($(\'[name="atknoclose"]\') != null) $(\'[name="atknoclose"]\').show();
            
            var old_val = document.getElementById("'.$name.'").value;
            var old_var_arr = old_val.split(\';\');
            old_var_arr.push(file.fileName);
            $("#'.$name.'").val(old_var_arr.join(\';\'));
            chunkuploader_reload_box("'.$pocet_suborov.'","'.$but_name.'","'.$name.'","box_filelist_'.$uid.'", "'.$this->record_params['db'].'", "'.$this->record_params['table'].'", "'.$this->record_params['keyname'].'", '.$keyid.', "'.base64_encode($this->m_dir).'");
            
            chunkuploader_update_record("'.$name.'",null, "'.$this->record_params['db'].'", "'.$this->record_params['table'].'", "'.$this->record_params['keyname'].'", '.$keyid.', "'.base64_encode($this->m_dir).'", old_var_arr.join(\';\'));            

        });
        r'.$uid.'.on("fileAdded", function(file, event){
                 var pocet = r'.$uid.'.files.length;
                 r'.$uid.'.files[pocet-1].fileName = genUname2(r'.$uid.'.files[pocet-1].fileName);
                 r'.$uid.'.files[pocet-1].file.name = genUname2(r'.$uid.'.files[pocet-1].file.name);
                 r'.$uid.'.upload();

                 if ($(\'[name="atksaveandclose"]\') != null)$(\'[name="atksaveandclose"]\').hide();
                 if ($(\'[name="atksaveandnext"]\') != null) $(\'[name="atksaveandnext"]\').hide();
                 if ($(\'[name="atknoclose"]\') != null) $(\'[name="atknoclose"]\').hide();
                 
                 $("#bu_status'.$uid.'").html(\'<b style="color:orange">Nahrávam na server, prosím čakajte ..... <span id="ru_p'.$uid.'"></span></b>\');
        });

        r'.$uid.'.on("fileError", function(file, message){
            $("#bu_status'.$uid.'").html(\'<b style="color:red">Nastala chyba, súbor nebol nahraný !!!</b>\');
        });

        r'.$uid.'.on("fileProgress", function(file, evt)
        {
            $("#ru_p'.$uid.'").html(Math.round(file.progress()*100) + "%"); 
        });        

        //]]></script>'.PHP_EOL;


        $ret = '<div id="box_filelist_'.$uid.'" style="width:440px; border: 1px dotted #ccc; padding:5px; margin:5px 0px;"><em>Neboli vybrané žiadne súbory...</em></div>
        <input type="hidden" id="'.$name.'" name="'.$name.'" value="'.$value.'"> 
        <a href="#!" id="'.$but_name.'" name="'.$but_name.'" class="btn btn-success btn-sm" style="display: inline;"><i class="icon-file-upload2"></i> Vložiť súbor</a>
        <span class="d-inline" id="bu_filename'.$uid.'"></span><div class="d-inline" id="bu_status'.$uid.'"></div>';

        $ret .= $in;
        return $ret;


    }



    public function delete($record)
    {
        if ($this->hasFlag(self::AF_DELETE_FILE)) {
            $value = $record[$this->fieldName()];
            if (!empty($value)) {
                $arr = explode(';', $value);
                if (!empty($arr)) {
                    foreach ($arr as $filename) {
                        $filename = trim($filename, ';');
                        if (file_exists($this->m_dir . $filename)) {
                            @unlink($this->m_dir . $filename);
                        }
                    }
                }
            }
        }
        return true;
    }





}

