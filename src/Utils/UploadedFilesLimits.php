<?php
namespace Optimal\FileManaging\Utils;

use Optimal\FileManaging\Utils\IniInfo;

class UploadedFilesLimits
{

    // IMAGES
    const IMAGES_JPG = ["jpg","jpeg"];
    const IMAGES_png = ["png"];
    const IMAGES_gif = ["gif"];

    const IMAGES = ["jpg","jpeg","png","gif"];

    // DOCUMENTS
    const DOCUMENTS_MS_WORD = ["doc","docx"];
    const DOCUMENTS_MS_EXCEL = ["xls","xlsx"];
    const DOCUMENTS_MS_POWER = ["ppt","pptx"];

    const DOCUMENTS_MS = ["doc","docx","xls","xlsx","ppt","pptx"];

    const DOCUMENTS_OPEN_WORD = ["odt","ott"];
    const DOCUMENTS_OPEN_EXCEL = ["ods","sdc"];
    const DOCUMENTS_OPEN_POWER = ["odp","sdd"];

    const DOCUMENTS_OPEN = ["odt","ott","ods","sdc","odp","sdd"];

    const DOCUMENTS_PDF = ["pdf"];

    const DOCUMENTS = ["doc","docx","xls","xlsx","ppt","pptx","odt","ott","ods","sdc","odp","sdd","pdf"];

    // ARCHIVES
    const ARCHIVES_ZIP = ["zip"];
    const ARCHIVES_7Z = ["7z"];
    const ARCHIVES_RAR = ["rar"];

    const ARCHIVES = ["zip","7z","rar"];

    // IMPORT, EXPORT

    const IMP_EXP_CSV = ["csv"];
    const IMP_EXP_XML = ["xml"];
    const IMP_EXP_JSON = ["json"];

    const IMP_EXP = ["csv","xml","json"];

    // WEB
    const WEB = ["css","js","html"];

    // ALL
    const ALLOWED = ["jpg","jpeg","png","gif","doc","docx","xls","xlsx","ppt","pptx","odt","ott","ods","sdc","odp","sdd","pdf","zip","7z","rar","csv","xml","json","css","js","html"];

    // FILES
    const FILES = ["doc","docx","xls","xlsx","ppt","pptx","odt","ott","ods","sdc","odp","sdd","pdf","zip","7z","rar","csv","xml","json","css","js","html"];

    // DISALLOWED
    const DISALLOWED = ["bin","cgi","exe","pl","py","sh","bat","html","xhtml","css","ico","inc","hphp","module"];

    private $maxCount;
    private $maxFileSize;
    private $maxAllFilesSize;
    private $allowedExt = [];

    function __construct()
    {

        IniInfo::init();

        $this->maxCount = IniInfo::getMaxFilesCount();
        $this->maxFileSize = IniInfo::getMaxFileSize();
        $this->maxAllFilesSize = IniInfo::getPostMaxSize();
    }

    public function setMaxCount($count){

        if($count > IniInfo::getMaxFilesCount()){
            throw new \Exception("Chosen count is greater than is allowed in php ini");
        }

        if($count * $this->getMaxFileSize() > IniInfo::getPostMaxSize()){
            $this->maxFileSize = intval(IniInfo::getPostMaxSize() / $count);
        }

        $this->maxCount = $count;
    }

    public function setMaxFileSize($size){

        $bytes = IniInfo::toBytes($size);

        if($bytes > IniInfo::getMaxFileSize()){
            throw new \Exception("Chosen max file size is greater than is allowed in php ini");
        }

        if($this->maxCount * $bytes > IniInfo::getPostMaxSize()){
            $this->maxCount = intval(IniInfo::getPostMaxSize() / $bytes);
        }

        $this->maxFileSize = $bytes;

    }

    public function setMaxPostSize($size){

        $bytes = IniInfo::toBytes($size);

        if($bytes > IniInfo::getPostMaxSize()){
            throw new \Exception("Chosen max post size is greater than is allowed in php ini");
        }

        if($this->maxCount * $this->maxFileSize > $bytes){
            $this->maxCount = intval($bytes / $this->maxFileSize);
        }

        $this->maxAllFilesSize = $bytes;

    }

    public function addAllowedExtensions($exts = []){

        foreach ($exts as $ext){
            if(!in_array($ext, $this->getDisAllowedExtensios())) {
                if(!in_array($ext, $this->allowedExt)) {
                    array_push($this->allowedExt, $ext);
                }
            }
        }
    }

    public function getMaxFilesCount(){
        return $this->maxCount;
    }

    public function getMaxFileSize(){
        return $this->maxFileSize;
    }

    public function getMaxPostSize(){
        return $this->maxAllFilesSize;
    }

    public function getAllowedExtensions(){

        $allowed = $this->allowedExt;

        if(empty($allowed)){
            $allowed = self::ALLOWED;
        }

        return $allowed;
    }

    public function getDisAllowedExtensios(){
        return self::DISALLOWED;
    }

}