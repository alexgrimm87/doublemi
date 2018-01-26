<?php
/**
 * Created by PhpStorm.
 * User: N1
 * Date: 23.02.17
 * Time: 09:21
 */

namespace AdminBundle\Service;


class PagerService
{
    const TYPE_L_ARROW = 'left_arrow';
    const TYPE_R_ARROW = 'right_arrow';
    const TYPE_PAGE = 'page';
    const TYPE_DOTS = 'dots';

    private $onPage;

    private  $totalCount;

    private function getPagesElement($type,$title,$id=0,$active=false){
        return [
            'type'=>$type,
            'id'=>$id,
            'active'=> $active,
            'title'=>$title
        ];
    }

    /**
     * @param $currentPage
     * @return string
     */
    private function getPagesTitle($currentPage){
        return strval($currentPage);
    }

    /**
     * @param $total
     * @param $onPage
     * @return $this
     * @throws \Exception
     */
    public function setParams($total, $onPage){
        if($total<0 || $onPage<=0)
            throw new \Exception('Invalid parameters value');

        $this->onPage = intval($onPage);
        $this->totalCount = intval($total);

        return $this;
    }

    public function getPager($currpage=1){
        if(!$this->onPage)
            throw new \Exception('Invalid parameters value');

        if($this->onPage >= $this->totalCount)
            return [];


        $pagerarr = [];
        $pages = ceil($this->totalCount/$this->onPage);

        if($currpage>1)
            $pagerarr[] = $this->getPagesElement(self::TYPE_L_ARROW,'',$currpage-1);

        $pagerarr[] = $this->getPagesElement(self::TYPE_PAGE,$this->getPagesTitle(1),1,$currpage == 1) ;

        if($currpage<=4)
            for($i=2;$i<$currpage;$i++){
                if($i<=$pages){
                    $pagerarr[] = $this->getPagesElement(self::TYPE_PAGE,$this->getPagesTitle($i),$i);
                }
            }
        else
        {
            $pagerarr[] = $this->getPagesElement(self::TYPE_DOTS,'...');

            for($i=$currpage-2;$i<$currpage;$i++){
                if($i<=$pages){
                    $pagerarr[] = $this->getPagesElement(self::TYPE_PAGE,$this->getPagesTitle($i),$i);
                }
            }
        }

        if($currpage>1)
            $pagerarr[] = $this->getPagesElement(self::TYPE_PAGE,$this->getPagesTitle($currpage),$currpage,true);

        if($currpage<$pages){
            if($currpage>=$pages-3)
                for($i=$currpage+1;$i<=$pages;$i++) {
                    if ($i <= $pages) {
                        $pagerarr[] = $this->getPagesElement(self::TYPE_PAGE,$this->getPagesTitle($i),$i);
                    }
                }
            else
            {
                for($i=$currpage+1;$i<$currpage+3;$i++) {
                    if ($i <= $pages) {
                        $pagerarr[] = $this->getPagesElement(self::TYPE_PAGE,$this->getPagesTitle($i),$i);
                    }
                }


                $pagerarr[] = $this->getPagesElement(self::TYPE_DOTS,'...');

                $pagerarr[] = $this->getPagesElement(self::TYPE_PAGE,$this->getPagesTitle($pages),$pages);
            }

            $pagerarr[] = $this->getPagesElement(self::TYPE_R_ARROW,'',$currpage+1);
        }

        return $pagerarr;
    }
}