<?php
namespace MainBundle\Controller;


use MainBundle\MainBundle;

class FileController
{
    const PIC_TYPE = 'picture';
    const FILE_TYPE = 'file';
    const PDF_TYPE = 'pdf';
    const VIDEO_TYPE = 'video';

    static function upload($file,$folder,$expectedType=false){
        if(!$file || !is_object($file))
            throw new \Exception('Пустые данные');


        if($file->getError()){
            throw new \Exception('Загруженный файл "'.$file->getClientOriginalName().'" слишком большой. Пожалуйста, попробуйте загрузить файл меньшего размера.');
        }

        $expansion = self::getExpansion($file->getMimeType());

        if(
            !$expansion
            ||
            ($expectedType && ((is_array($expectedType) && !in_array($expansion,$expectedType))
                    ||
                    (!is_array($expectedType) && $expectedType != $expansion ))
            )
        ){
            throw  new \Exception('Недопустимый формат файла: "'.$file->getClientOriginalName().'".');
        }

        $res = [];
        $folder = sprintf('upload/%s/',$folder);
        $folderPath = str_replace('//','/',sprintf('%s/../../../web/%s',__DIR__,$folder));

        $format = strtolower(substr($file->getClientOriginalName(), strripos($file->getClientOriginalName(), '.') + 1));

        if ($format == 'jpeg') {
            $format = 'jpg';
        }

        $newName = md5(
            implode(
                rand(10,99),
                array(
                    time(),
                    'stuff',
                    'word'
                )
            )
        );

        $filename = $newName . '.' . $format;
        $file->move($folderPath, $filename);

        return '/'.$folder.$filename; //   /upload/file_dir/file.name
    }

    /**
     * @param string $type
     * @return bool|string
     */
    static function getExpansion($type){
        $types = [
            self::PIC_TYPE => [
                'image/png',
                'image/jpeg',
                'image/gif'
            ],
            self::VIDEO_TYPE=>[
                'video/msvideo',
                'video/avi',
                'video/x-msvideo',
                'video/mpeg',
                'video/quicktime'
            ],
            self::PDF_TYPE => [
                'application/pdf'
            ],
            self::FILE_TYPE => [
                'application/pdf',
                'application/msword',
                'application/x-excel',
                'application/excel',
                'application/vnd.ms-excel',
                'application/x-msexcel',
                'application/rtf',
                'application/x-rtf',
                'text/richtext',
                'text/plain',
                'powerpoint',
                'image/vnd.djvu',
                'image/x-djvu',
                'application/mspowerpoint',
                'application/vnd.ms-powerpoint',
                'model/x-pov',
                'application/vnd.ms-office',
                'application/mspowerpoint',
                'application/vnd.ms-powerpoint',
                'application/mspowerpoint',
                'application/powerpoint',
                'application/vnd.ms-powerpoint',
                'application/x-mspowerpoint',
                'application/mspowerpoint',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.template'
            ]
        ];

        foreach ($types as $key=>$typesArray){
            if(in_array($type,$typesArray)) {
                return $key;
            }
        }

        return false;
    }
}