<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use AdminBundle\Controller\PageController;
use Symfony\Component\Form\Button;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Form;
use Doctrine\DBAL\Exception\SyntaxErrorException;

class BaseController extends Controller
{
    public function getBases(Request $request=null){
        $result = [
            'base'=>true
        ];

        $this->container->get('main.menu')->init($this->container); // menu

        //seo
        if($request){
            $code = $request->get('_route');
            $seo = $this->getDoctrine()->getRepository('MainBundle:SEO')->findOneBy(['code'=>$code,'enabled'=>1]);
            if($seo){
                $this->get('main.seo')->update($seo);
            }
        }

        //params
        $params = $this->container->get('admin.config');
        $result['params']['social'] = $params->getData('social');
        $result['params']['contact'] = $params->getData('contact');
        $result['params']['config'] = $params->getData('config');

        return $result;
    }

    function getPaymentButton($successUrl, $sum, $hint){
        $params = http_build_query([
            'account'=>'410013984392899',
            'quickpay'=>'shop',
            'payment-type-choice'=>'off',
            'writer'=>'buyer',
            'targets-hint'=>$hint,
            'default-sum'=>$sum,
            'button-text'=>'01',
            'comment'=>'on',
            'hint'=>'',
            'fio'=>'on',
            'successURL'=>$successUrl
        ]);

        return'<iframe frameborder="0" allowtransparency="true" scrolling="no" src="https://money.yandex.ru/quickpay/shop-widget?'.$params.'" width="450" height="270"></iframe>';
    }

    /**
     * @param string $key
     * @param string $repository
     * @param array $fields
     * @param array $requestSpecParams
     * @param array $specCallback
     * @param array $injection
     * @return array
     */
    public function customMatching($key='', $repository='', $fields = [], $requestSpecParams=[],$specCallback=[],$injection=[]){
        $resp = self::getDefaultMatch();

        /**
         * @var EntityRepository $repo
         */
        $repo = $this->getDoctrine()->getRepository($repository);
        if(!$repo)
            throw new NotFoundHttpException('EntityRepository not found');

        $arrayCallBack = $this->getCallBacks();
        //entity fields set
        $metaData = $this->getDoctrine()->getManager()->getClassMetadata($repo->getClassName());
        $fieldSet = array_merge(
            $metaData->fieldMappings,
            $metaData->associationMappings
        );

        $tableAlt = $metaData->table['name'];
        $tableAlt = "`$tableAlt`";
        $filterFields = (isset($fields[$key])?$fields[$key]:[]);

        $params = [' 1'];
        if(isset($injection['params']))
            $params = array_merge($params, $injection['params']);
        $totalParams = [' 1'];

        $select = [
            'main'=>["$tableAlt.id as id"],
            'total'=>["COUNT( DISTINCT $tableAlt.`id`) as cnt"],
            'spec'=>["COUNT(  DISTINCT $tableAlt.`id`) as cnt"]
        ];

        $concatenationType = [];
        if(isset($injection['concatenation']) && is_array($injection['concatenation']))
            $concatenationType = $injection['concatenation'];

        $laxParameters = [];
        if(isset($fields['_laxParameters'])){
            if(is_array($fields['_laxParameters']))
                $laxParameters = $fields['_laxParameters'];
            elseif (is_string($fields['_laxParameters']))
                $laxParameters = [$fields['_laxParameters']];
        }

        //join
        $join = isset($injection['join'])?$injection['join']:[];
        foreach ($fieldSet as $field){
            if(isset($filterFields[$field['fieldName']])){
                $val = false;
                if(!is_array($filterFields[$field['fieldName']]))
                    $filterFields[$field['fieldName']] = [$filterFields[$field['fieldName']]];

                $altCall = false;
                if(isset($specCallback[$field['fieldName']]) && isset($arrayCallBack[$field['type'].'Spec']))
                    $altCall = $field['type'].'Spec';

                //build query param
                switch ($field['type']){
                    case 4: //time design
                        if(is_array($filterFields[$field['fieldName']])) {
                            $jMeta = $this->getDoctrine()->getManager()->getClassMetadata($field['targetEntity']);
                            $jFieldSet = array_merge(
                                $jMeta->fieldMappings,
                                $jMeta->associationMappings
                            );
                            if(isset($jFieldSet[$field['mappedBy']])) {
                                $cols = [];
                                foreach ($jFieldSet[$field['mappedBy']]['targetToSourceKeyColumns'] as $base=>$jCol){
                                    $cols[] = sprintf(' %s.`%s`=`%s`.`%s`',$tableAlt,$base,$jMeta->table['name'],$jCol);
                                }
                                $jStr = sprintf('LEFT JOIN `%s` ON %s',$jMeta->table['name'],implode(' AND',$cols));

                                $jParams = [];
                                foreach ($filterFields[$field['fieldName']] as $k=>$v){
                                    if(isset($jFieldSet[$k])){
                                        if(is_array($v)){
                                            $v = array_map(function($a){return intval($a);},$v);
                                        } else {
                                            $v = [intval($v)];
                                        }

                                        $jParams[] = sprintf(" `%s`.`%s` IN (%s)",$jMeta->table['name'],$jFieldSet[$k]['fieldName'],implode(', ',$v));
                                    }
                                }
                                if($jParams){
                                    $join[] = $jStr;
                                    $val = implode(' AND', $jParams);
                                }
                            }
                        }
                        break;
                    case 8:
                        $join[] = $this->buildJoin($tableAlt,$field);
                        $joinTable = $field['joinTable']['name'];
                        $joinCols = [];
                        foreach ($field['joinTable']['inverseJoinColumns'] as $col){
                            $joinCols[]  = $col['name'];
                        }
                        if(
                            (count($filterFields[$field['fieldName']])==1 && empty($filterFields[$field['fieldName']][0]))
                            ||
                            (isset($filterFields[$field['fieldName']][0])?in_array($filterFields[$field['fieldName']][0],['null','Null','NULL']):false)
                            ||
                            in_array($filterFields[$field['fieldName']],['null','Null','NULL'])
                        ){
                            $val = [];
                            foreach ($joinCols as $c){
                                $val[] = sprintf(" `%s`.`%s` IS NULL",$joinTable,$c);
                            }
                            if($val) {
                                $val = sprintf(" (%s)",implode(' OR',$val));
                            }
                            break;
                        }
                        $val = array_map($arrayCallBack[($altCall?:'integer')],$filterFields[$field['fieldName']]);
                        $val = array_unique($val);

                        $orArray = [];
                        foreach ($joinCols as $c){
                            $orArray[] = sprintf(" `%s`.`%s` IN (%s)", $joinTable, $c, implode(', ', $val));
                        }

                        $val = sprintf(' (%s)',implode(' OR',$orArray));

                        break;
                    case 'integer':
                        //sector check
                        if($check = $this->checkSector($filterFields[$field['fieldName']],$arrayCallBack[($altCall?:'integer')])){
                            $val = sprintf($check,$tableAlt,$field['fieldName']);
                            break;
                        }
                    case 2: // entity type from associationMappings
                        $joinTable = false;
                        $metaData = false;
                        if($field['type'] == 2 && is_array($filterFields[$field['fieldName']]) && $filterFields[$field['fieldName']] != array_values($filterFields[$field['fieldName']]) ){
                            $subRepo = $this->getDoctrine()->getRepository($field['targetEntity']);
                            if($subRepo) {
                                $metaData = $this->getDoctrine()->getManager()->getClassMetadata($subRepo->getClassName());
                                $join[] = $this->buildJoin($tableAlt, $field,$metaData);
                                $joinTable = $metaData->table['name'];
                            }
                        }

                        $f = isset($field['targetToSourceKeyColumns'])?$field['targetToSourceKeyColumns']:[];
                        $field_title = $f ? array_pop($f) : $field['fieldName'];

                        if(
                            !$joinTable
                            &&
                            (
                                (count($filterFields[$field['fieldName']])==1 && empty($filterFields[$field['fieldName']][0]))
                                ||
                                (isset($filterFields[$field['fieldName']][0])?in_array($filterFields[$field['fieldName']][0],['null','Null','NULL']):false)
                                ||
                                in_array($filterFields[$field['fieldName']],['null','Null','NULL'])
                            )
                        ){
                            $val = sprintf(' %s.`%s` IS NULL', $tableAlt, $field_title);
                            break;
                        }

                        if($joinTable && $metaData){
                            $val = [];
                            foreach ($filterFields[$field['fieldName']] as $key=>$v){
                                if(in_array($key,$metaData->fieldNames)){
                                    if(!is_array($v))
                                        $v = [$v];

                                    $value_std = $metaData->fieldMappings[$key]['type'];
                                    if(in_array($value_std,['array','text'])){
                                        $value_std = 'string';
                                    }

                                    $value_std = isset($arrayCallBack[$value_std])?$arrayCallBack[$value_std]:false;

                                    if($value_std) {
                                        ;                                       $value_std = array_map($value_std, $v);

                                        $subVals = [];
                                        foreach ($value_std as $innerValue) {

                                            if (is_string($innerValue)) {
                                                $subVals[] = sprintf(' %s.`%s` LIKE %s', $joinTable, $metaData->fieldMappings[$key]['columnName'], $innerValue);
                                            } else {
                                                $subVals[] = sprintf(' %s.`%s` = %s', $joinTable, $metaData->fieldMappings[$key]['columnName'], $innerValue);
                                            }

                                        }
                                        if($subVals){
                                            $val[] = sprintf(' (%s)',implode(' OR',$subVals));
                                        }
                                    }
                                }
                            }

                            $val = $val?implode(' AND',$val):'';
                        } else {
                            $val = array_map($arrayCallBack[($altCall ?: 'integer')], $filterFields[$field['fieldName']]);
                            $val = array_unique($val);
                            $val = sprintf(' %s.`%s` IN (%s)', $tableAlt, $field_title, implode(', ', $val));
                        }
                        break;
                    case 'float':
                        //sector check
                        if($check = $this->checkSector($filterFields[$field['fieldName']],$arrayCallBack[($altCall?:'float')])){
                            $val = sprintf($check,$tableAlt,$field['fieldName']);
                            break;
                        }
                        if(
                            (count($filterFields[$field['fieldName']])==1 && empty($filterFields[$field['fieldName']][0]))
                            ||
                            in_array($filterFields[$field['fieldName']][0],['null','Null','NULL'])
                            ||
                            in_array($filterFields[$field['fieldName']],['null','Null','NULL'])
                        ){
                            $val = sprintf(' %s.`%s` IS NULL',$tableAlt,$field['fieldName']);
                            break;
                        }
                        $val = array_map($arrayCallBack[($altCall?:'float')],$filterFields[$field['fieldName']]);
                        $val = array_unique($val);

                        $val = sprintf(' %s.`%s` IN (%s)',$tableAlt,$field['fieldName'],implode(', ',$val));

                        break;
                    case 'datetime':
                        if($check = $this->checkSector($filterFields[$field['fieldName']],$arrayCallBack[($altCall?:'datetimefull')])){
                            $val = sprintf($check,$tableAlt,$field['fieldName']);
                            break;
                        }
                    case 'array':
                    case 'text':
                    case 'string':
                        $val = array_map($arrayCallBack[($altCall?:$field['type'])],$filterFields[$field['fieldName']]);
                        $val = array_filter($val);
                        $val = array_unique($val);
                        foreach ($val as $key=>$elem){
                            $val[$key] = " LOWER({$tableAlt}.`{$field['fieldName']}`) LIKE LOWER({$elem})";
                        }
                        if($val){
                            $type = ' OR';
                            if(isset($concatenationType[$field['fieldName']]))
                                $type = $concatenationType[$field['fieldName']];
                            $val = sprintf(" (%s)",implode($type,$val));
                        }
                        break;
                    case 'boolean':
                        $val = $arrayCallBack[($altCall?:$field['type'])]($filterFields[$field['fieldName']]);
                        $val = " {$tableAlt}.`{$field['fieldName']}` = $val";
                        break;
                }

                if($val){
                    if(!in_array($field['fieldName'],$laxParameters)){
                        $params[] = $val;
                    }
                }
            }
            if(isset($requestSpecParams[$field['fieldName']])){
                $s = $this->buildSpecParamsString($tableAlt,$field,$requestSpecParams[$field['fieldName']]);
                $params[] = $s;
                $totalParams[] = $s;
            }
        }

        $sort = [];

        if(isset($fields['_sort']) && is_array($fields['_sort'])){
            foreach ($fields['_sort'] as $key=>$param){
                $param = (in_array($param,['DESC','desc','Desc'])?'DESC':'ASC');
                if(isset($fieldSet[$key])){
                    $sort[] = " {$tableAlt}.`{$key}` $param";
                }
            }
        }

        $offset = (isset($fields['_offset']) && $fields['_offset']>0?intval($fields['_offset']):0);
        $limit = (isset($fields['_limit']) && $fields['_limit']>0?intval($fields['_limit']):30);

        //price str
        $join = array_unique($join);

        //build query
        $query = sprintf(
            'SELECT %s FROM %s %s WHERE %s GROUP BY id %s LIMIT %d,%d',
            implode(', ',$select['main']),
            $tableAlt,
            implode(' ',$join),
            implode(' AND',$params),
            ($sort?'ORDER BY '.implode(' ,',$sort):''),
            $offset,
            $limit
        );

        try {
            $stmt = $this->getDoctrine()->getManager()
                ->getConnection()
                ->prepare(
                    $query
                );
            $stmt->execute();
            $ideas = $stmt->fetchAll();

        }catch (SyntaxErrorException $e){
            return $resp;
        }

        if(!$ideas)
            return $resp;

        $ideas = array_map($arrayCallBack['_response'],$ideas);

        $obj = $repo->findBy(['id'=>$ideas]);

        //resort response
        $ideas_copy = $ideas;
        foreach ($obj as $element){
            if(false !== $key = array_search($element->getId(),$ideas_copy))
                $ideas[$key] = $element;
        }

        $obj = array_filter($ideas,$arrayCallBack['_resort']);
        $resp['items'] = $obj;

        //selected count
        $selectedTotal = sprintf(
            'SELECT %s FROM %s %s WHERE %s ',
            implode(', ',$select['spec']),
            $tableAlt,
            implode(' ',$join),
            implode(' AND',$params)
        );
        $ideasSpec = null;

        try {
            $stmt = $this->getDoctrine()->getManager()
                ->getConnection()
                ->prepare(
                    $selectedTotal
                );
            $stmt->execute();
            $ideasSpec = $stmt->fetchAll();
        }catch (SyntaxErrorException $e){

        }
        $resp['meta']['total'] = ($ideasSpec && isset($ideasSpec[0]) && isset($ideasSpec[0]['cnt'])?intval($ideasSpec[0]['cnt']):0);
        return $resp;
    }

    private function checkSector($val,$callback){

        if(!is_array($val) || (!isset($val['from']) && !isset($val['to'])))
            return false;

        $from = (isset($val['from'])?$callback($val['from']):null);
        $to   = (isset($val['to'])  ?$callback($val['to'])  :null);

        $f = [];
        if($from){
            $f[] = ' %1$s.`%2$s` >= '.$from;
        }
        if($to){
            $f[] = ' %1$s.`%2$s` <= '.$to;
        }
        return implode(' AND',$f);
    }

    private function buildJoin($tableAlt,$field, $meta=[]){
        $colomns = [];
        if(isset($field['joinTable'])) {
            foreach ($field['joinTable']['joinColumns'] as $col) {
                $colomns[] = sprintf(" %s.`%s`=`%s`.`%s`", $tableAlt, $col['referencedColumnName'], $field['joinTable']['name'], $col['name']);
            }

            return sprintf('LEFT JOIN `%s` ON %s', $field['joinTable']['name'], implode(' AND', $colomns));
        } elseif($meta) {

            foreach ($field['joinColumns'] as $col) {
                $colomns[] = sprintf(" %s.`%s`=`%s`.`%s`", $tableAlt, $col['name'], $meta->table['name'], $col['referencedColumnName']);
            }

            return sprintf('LEFT JOIN `%s` ON %s', $meta->table['name'], implode(' AND', $colomns));
        }
    }

    private function buildSpecParamsString($tableAlt,$field,$requestSpecParams){

        $glue = ' OR';
        if(isset($requestSpecParams['AND'])){
            $glue = ' AND';
            $requestSpecParams = $requestSpecParams['AND'];
        }

        if($requestSpecParams!==0 && $requestSpecParams!=='0' && !$requestSpecParams)
            return ' 1';

        if(is_array($requestSpecParams)){
            $l = [];
            foreach ($requestSpecParams as $rsp){
                $l[] = $this->buildSpecParamsString($tableAlt,$field,$rsp);
            }
            return sprintf(' (%s)',implode($glue,$l));
        }
        if(in_array($field['type'],['array','text','string'])){
            return sprintf(' LOWER(%s.`%s`) %s',$tableAlt,$field['fieldName'],$requestSpecParams);
        }
        return sprintf(' %s.`%s` %s',$tableAlt,$field['fieldName'],$requestSpecParams);
    }

    static function getDefaultMatch(){
        return [
            'items'=>[],
            'meta'=>[
                'total'=>0
            ]
        ];
    }

    public function getCallBacks(){
        return [
            'boolean' => function($a){
                if(is_array($a) && isset($a[0])){
                    return (boolval($a[0])?1:0);
                }
                return (boolval($a)?1:0);
            },
            'integer' => function($a){
                return intval($a);
            },
            'float' => function($a){
                return floatval($a);
            },
            'string' => function($a){
                $a = strval((is_array($a)?'':$a));
                $a = str_replace(['\'','"','`','%','_',';'],['_','_','_','\%','\_'],$a);
                return $a?"'%$a%'":'';
            },
            'datetimefull'=>function($a){
                $a = strval((is_array($a)?'':$a));
                $time = strtotime($a);
                if(!$time)
                    return false;
                return date("'Y-m-d H:i:s'",$time);
            },
            'datetime' => function($a){
                $a = strval((is_array($a)?'':$a));
                $time = strtotime($a);
                if(!$time)
                    return false;
                return date("'Y-m-d%'",$time);
            },
            'array' => function($a){
                $a = strval((is_array($a)?'':$a));
                $a = str_replace(['\'','"','`','%','_',';'],['_','_','_','\%','\_'],$a);
                return $a?"'%\"$a\"%'":'';
            },
            'arraySpec'=>function($a){
                $a = strval((is_array($a)?'':$a));
                $a = str_replace(['\'','"','`','%','_',';'],['_','_','_','\%','\_'],$a);
                return $a?"%\"%$a%\"%":'';
            },
            '_response' => function($a){
                return (isset($a['id'])?$a['id']:0);
            },
            '_resort' => function($a){
                return is_object($a);
            }
        ];
    }



    public function pageNavigationParams(Request $request, &$filter, &$page, &$limit){
        // page params
        $page = intval($request->get('_page'));
        if(!$page || $page<1)
            $page = 1;

        $offset = $limit * ($page - 1);
        $filter['_limit'] = $limit;
        $filter['_offset'] = $offset;
    }
}