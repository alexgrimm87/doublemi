<?php
namespace AdminBundle\Controller;

use AdminBundle\Form\Type\ArrayObjectType;
use AdminBundle\Form\Type\FileselectType;
use AdminBundle\Form\Type\FileSliderType;
use AdminBundle\Page\DataBaseAdmin;
use Ivory\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

class ConfigController extends PageController
{
    public function indexAction(Request $request)
    {
        $this->sourceDir = $this->get('admin.config')->getSource();

        $dirFiles = scandir($this->sourceDir);
        $fileList = [];
        $filePathList = [];
        $pattern = '/\.json$/';
        foreach ($dirFiles as $fileName){
            if(preg_match($pattern,$fileName)){
                $key = preg_replace($pattern,'',$fileName);

                /// mod and save file data
                //if($key == 'bouquet_subscribe') {
                //    $arr = [
                //        'name' => 'Подписка на цветы',
                //        'title' => [
                //            'value' => 'Свежие цветы для любимой каждую неделю',
                //            'type' => TextType::class,
                //            'params' => [
                //                'required' => true
                //            ]
                //        ],
                //        "picture" => [
                //            "value" => "/upload/Доставка/perfect-bouquet.png",
                //            "type" => FileselectType::class,
                //            "params" => []
                //        ],
                //        "picture_text" => [
                //            "value" => "<p>Эксклюзивный <br><span style=\"font-size:20px\"><strong>букет</strong></span> <br>для Вашей <br>любимой</p>",
                //            "type" => CKEditorType::class,
                //            "params" => [
                //                "label" => "Надпись на картинке"
                //            ]
                //        ],
                //        'description' => [
                //            "value" => "",
                //            "type" => CKEditorType::class,
                //            "params" => [
//
                //            ]
                //        ],
                //        'text' => [
                //            "value" => "",
                //            "type" => CKEditorType::class,
                //            "params" => [
//
                //            ]
                //        ],
                //        'block_1_show' => [
                //            'value' => true,
                //            'type' => CheckboxType::class,
                //            'params' => [
                //                'label' => 'Отображать блок #1',
                //                'required' => false
                //            ]
                //        ],
                //        'block_1_title' => [
                //            'value' => 'Цветы к её ногам',
                //            'type' => TextType::class,
                //            'params' => [
                //                'required' => true,
                //                'label' => 'Название блока #1'
                //            ]
                //        ],
                //        "block_1_picture" => [
                //            "value" => "/images/flowers-her.jpg",
                //            "type" => FileselectType::class,
                //            "params" => [
                //                'label' => 'Картинка блока #1'
                //            ]
                //        ],
                //        "block_1_text" => [
                //            "value" => "",
                //            "type" => CKEditorType::class,
                //            "params" => [
                //                'label' => 'Текст блока #1'
                //            ]
                //        ],
                //        'block_2_show' => [
                //            'value' => true,
                //            'type' => CheckboxType::class,
                //            'params' => [
                //                'label' => 'Отображать блок #2',
                //                'required' => false
                //            ]
                //        ],
                //        'block_2_title' => [
                //            'value' => 'Множество вариантов букетов',
                //            'type' => TextType::class,
                //            'params' => [
                //                'required' => true,
                //                'label' => 'Название блока #2'
                //            ]
                //        ],
                //        "block_2_picture" => [
                //            "value" => "/images/bouquet-set.jpg",
                //            "type" => FileselectType::class,
                //            "params" => [
                //                'label' => 'Картинка блока #2'
                //            ]
                //        ],
                //        "block_2_text" => [
                //            "value" => "",
                //            "type" => CKEditorType::class,
                //            "params" => [
                //                'label' => 'Текст блока #2'
                //            ]
                //        ],
                //        'portfolio_show' => [
                //            'value' => true,
                //            'type' => CheckboxType::class,
                //            'params' => [
                //                'label' => 'Отображать блок портфолио',
                //                'required' => false
                //            ]
                //        ],
                //        'review_show' => [
                //            'value' => true,
                //            'type' => CheckboxType::class,
                //            'params' => [
                //                'label' => 'Отображать блок отзывов',
                //                'required' => false
                //            ]
                //        ]
                //    ];
                //    file_put_contents($this->sourceDir . $fileName, json_encode($arr));
                //};

                $fileList[$key] = json_decode(file_get_contents($this->sourceDir.$fileName),1);
                $filePathList[$key] = $this->sourceDir.$fileName;
            }
        }

        //check if edit
        $file = $request->query->get('file');

        if($file && !isset($fileList[$file]))
            return $this->redirectToRoute('admin_config');


        $info = new DataBaseAdmin();

        $info->data['fileList'] = &$fileList;
        // get menu
        $this->get('admin.pages')->getMenu($info,$this);

        if($file){
            $info->data['file'] = &$fileList[$file];

            //build data and form
            $data=[];
            foreach ($fileList[$file] as $key=>$field){
                if(isset($field['value'])){
                    $data[$key] = $field['value'];
                }
            }

            $form=$this->createFormBuilder($data);

            foreach ($fileList[$file] as $key=>$field){
                if(isset($field['type']) && isset($field['params'])){
                    $form->add($key,$field['type'],$field['params']);
                }
            }
            $form->add('save', SubmitType::class, array('label' => 'Сохранить','attr'=>['class'=>'btn btn-success']));
            $form = $form->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                //update
                foreach ($info->data['file'] as $key=>$field){
                    if(isset($data[$key])){
                        $info->data['file'][$key]['value'] = $data[$key];
                    }
                }
                //save
                file_put_contents($filePathList[$file],json_encode($info->data['file']));
            }

            $info->data['form'] = $form->createView();

            return $this->render('AdminBundle:Admin:config_edit.html.twig',['admin'=>$info]);
        }

        return $this->render('AdminBundle:Admin:config_list.html.twig',['admin'=>$info]);
    }
}