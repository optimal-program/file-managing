<?php
/**
 * Created by PhpStorm.
 * User: radim
 * Date: 08.10.2017
 * Time: 14:09
 */

namespace Optimal\FileManaging\Renderers;
use Optimal\FileManaging\FileObject\File;
use Optimal\FileManaging\FileObject\Image;
//use App\Core\Utils\HtmlElement;
//use App\Core\Utils\Icons\IconsFactory;
//use App\Core\View;

class FileRenderer extends HtmlElement {

    protected $fileSource;
    protected $previewSource;

    protected $linkTo;
    protected $title;
    protected $time;
    protected $additionalInfo;

    function __construct(File $fileSource)
    {

        if($fileSource == null){
            throw new \Exception("No file source defined");
        }

        parent::__construct();

        $this->previewSource = null;
        $this->fileSource = $fileSource;
        $this->time = time();
        $this->additionalInfo = null;
    }

    public function setFileSource(File $fileSource){
        $this->fileSource = $fileSource;
    }

    public function setPreviewSource(Image $preview = null){
        $this->previewSource = $preview;
    }

    public function getPreviewSource(){
        if($this->previewSource != null) {
            return $this->previewSource;
        } else {
            return $this->fileSource;
        }
    }

    public function changePreviewTime(){
        $this->time = filemtime($this->fileSource->getRealPath());
    }

    /**
     * @param $style
     * @return $this
     */
    public function setStyle($style){
        $this->setAttribute("style", $style);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStyle(){
        return $this->getAttributeVal("style");
    }

    /**
     * @param $width
     * @return $this
     */
    public function setMaxWidth($width){
        $this->setAttribute("style", $this->getAttributeVal("style")."max-width:".$width.";");
        return $this;
    }

    /**
     * @param $height
     * @return $this
     */
    public function setMaxHeight($height){
        $this->setAttribute("style", $this->getAttributeVal("style")."max-height:".$height.";");
        return $this;
    }

    /**
     * @param $link
     * @return $this
     */
    public function setLinkTo($link){
        $this->linkTo = $link;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getAttributeVal("title");
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setAttribute("title", $title);
        $this->setAttribute("alt", $title);
        return $this;
    }

    /**
     * @param string $additionalInfo
     * @return $this
     */
    public function setAdditionalInfo($additionalInfo = ""){
        $this->additionalInfo = $additionalInfo;
        return $this;
    }

    public function getAttributes()
    {
        $attributes = parent::getAttributes();

        if($this->previewSource != null){
            $attributes["src"] = $this->previewSource->getUrlToFile()."?time=".$this->time;
        } else {
            $icons = IconsFactory::init();
            $icon = $icons->getImageIcon($this->fileSource->getRealExtension());
            $attributes["src"] = $icon->getSrc()."?time=".$this->time;
        }

        if($this->getAttributeVal("title") == ""){
            $attributes["title"] = $this->fileSource->getRealNameEx();
            $attributes["alt"] = $this->fileSource->getRealNameEx();
        }

        return $attributes;
    }

    public function render(){

        $view = new View(__DIR__."/", "file");

        $view->attributes = $this->renderAttributes($this->getAttributes());
        $view->additionalInfo = $this->fileSource->parseAdditionalInformation($this->additionalInfo);

        $linkTo = ["",""];
        if($this->linkTo != ""){
            $linkTo[0] = '<a href="'.$this->linkTo.'" target="_blank">';
            $linkTo[1] = '</a>';
        }

        $view->linkTo = $linkTo;
        return $view->display();

    }
}
