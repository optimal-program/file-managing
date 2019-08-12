<?php
/**
 * Created by PhpStorm.
 * User: radim
 * Date: 08.10.2017
 * Time: 14:09
 */

namespace Optimal\FileManaging\Renderers;

use Optimal\FileManaging\resources\Image;
//use App\Core\View;

final class ImageRenderer extends FileRenderer {

    private $lightbox;
    private $lightboxGroup;

    function __construct(Image $imageSource)
    {
        parent::__construct($imageSource);

        $this->title = "";
        $this->enableLightbox(false);
        $this->setLightBoxGroup();
    }

    /**
     * @param bool $enable
     */
    public function enableLightbox($enable = true){
        $this->lightbox = $enable;
    }

    /**
     * @param string $lightboxGroup
     * @return $this
     */
    public function setLightBoxGroup($lightboxGroup = "photos"){
        $this->lightboxGroup = $lightboxGroup;
        return $this;
    }

    public function getAttributes()
    {
        $attributes = parent::getAttributes();

        if($this->previewSource == null){
            $attributes["src"] = $this->fileSource->getUrlToFile()."?time=".$this->time;
        }

        return $attributes;
    }

    public function render()
    {

        $view = new View(__DIR__."/", "image");
        $view->attributes = $this->renderAttributes($this->getAttributes());

        $linkTo = ["",""];
        if($this->linkTo != ""){
            if($this->lightbox){
                $linkTo[0] = '<a href="'.$this->fileSource->getUrlToFile().'" data-lightbox="'.$this->lightboxGroup.'">';
                $linkTo[1] = "</a>";
            } else {
                $linkTo[0] = '<a href="'.$this->linkTo.'">';
                $linkTo[1] = '</a>';
            }
        }

        $view->linkTo = $linkTo;

        return $view->display();
    }

}