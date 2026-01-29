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
    const array IMAGES_JPG  = ["jpg", "jpeg", "jfif"];
    const array IMAGES_WEBP = ["webp"];
    const array IMAGES_PNG = ["png"];
    const array IMAGES_GIF  = ["gif"];
    const  array IMAGES_SVG = ["gif"];

    const array BITMAP_IMAGES = ["jfif", "webp", "jpg", "jpeg", "png", "gif"];
    const array VECTOR_IMAGES = ["svg"];
    const array IMAGES        = ["jfif", "webp", "jpg", "jpeg", "png", "gif", "svg"];

    // DOCUMENTS
    const array DOCUMENTS_MS_WORD  = ["doc", "docx"];
    const array DOCUMENTS_MS_EXCEL = ["xls", "xlsx"];
    const array DOCUMENTS_MS_POWER = ["ppt", "pptx"];

    const array DOCUMENTS_MS = ["doc", "docx", "xls", "xlsx", "ppt", "pptx"];

    const array DOCUMENTS_OPEN_WORD  = ["odt", "ott"];
    const array DOCUMENTS_OPEN_EXCEL = ["ods", "sdc"];
    const array DOCUMENTS_OPEN_POWER = ["odp", "sdd"];

    const array DOCUMENTS_OPEN = ["odt", "ott", "ods", "sdc", "odp", "sdd"];

    const array DOCUMENTS_PDF = ["pdf"];

    const array DOCUMENTS = ["doc", "docx", "xls", "xlsx", "ppt", "pptx", "odt", "ott", "ods", "sdc", "odp", "sdd", "pdf"];

    // ARCHIVES
    const array ARCHIVES_ZIP = ["zip"];
    const array ARCHIVES_7Z  = ["7z"];
    const array ARCHIVES_RAR = ["rar"];

    const array ARCHIVES = ["zip", "7z", "rar"];

    // IMPORT, EXPORT

    const array IMP_EXP_CSV  = ["csv"];
    const array IMP_EXP_XML  = ["xml"];
    const array IMP_EXP_JSON = ["json"];

    const array IMP_EXP = ["csv", "xml", "json"];

    // ALL
    const array ALL_SUPPORTED_FILES = [
        "jpg",
        "jfif",
        "webp",
        "jpeg",
        "png",
        "gif",
        "svg",
        "doc",
        "docx",
        "xls",
        "xlsx",
        "ppt",
        "pptx",
        "odt",
        "ott",
        "ods",
        "sdc",
        "odp",
        "sdd",
        "pdf",
        "zip",
        "7z",
        "rar",
        "csv",
        "xml",
        "json",
        "css",
        "js",
        "html"
    ];

    // FILES
    const array NO_IMAGES = [
        "doc",
        "docx",
        "xls",
        "xlsx",
        "ppt",
        "pptx",
        "odt",
        "ott",
        "ods",
        "sdc",
        "odp",
        "sdd",
        "pdf",
        "zip",
        "7z",
        "rar",
        "csv",
        "xml",
        "json",
        "css",
        "js",
        "html"
    ];

    // DISALLOWED
    const array DISALLOWED = [
        "bin",
        "cgi",
        "exe",
        "pl",
        "py",
        "sh",
        "bat",
        "html",
        "xhtml",
        "ico",
        "inc",
        "hphp",
        "module",
        "dll",
        "js",
        "css"
    ];

}