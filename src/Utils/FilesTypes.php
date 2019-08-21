<?php
/**
 * Created by PhpStorm.
 * User: radim
 * Date: 21.08.2019
 * Time: 10:39
 */

namespace Optimal\FileManaging\Utils;

final class FilesTypes
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
    const ALL_SUPPORTED_FILES = ["jpg","jpeg","png","gif","doc","docx","xls","xlsx","ppt","pptx","odt","ott","ods","sdc","odp","sdd","pdf","zip","7z","rar","csv","xml","json","css","js","html"];

    // FILES
    const NO_IMAGES = ["doc","docx","xls","xlsx","ppt","pptx","odt","ott","ods","sdc","odp","sdd","pdf","zip","7z","rar","csv","xml","json","css","js","html"];

    // DISALLOWED
    const DISALLOWED = ["bin","cgi","exe","pl","py","sh","bat","html","xhtml","css","ico","inc","hphp","module"];

}