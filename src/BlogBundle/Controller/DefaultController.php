<?php

namespace BlogBundle\Controller;


use BlogBundle\BlogBundle;
use BlogBundle\Entity\Post;
use Doctrine\DBAL\Exception\SyntaxErrorException;
use MainBundle\Controller\BaseController;
use MainBundle\Entity\CatalogSection;
use MainBundle\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class DefaultController extends BaseController
{
    const FILTER_KEY = 'filter';

    /**
     * @Route("/blog", name="blog_root")
     * @Route("/blog/tag/{tag}", name="blog_root_tag")
     *
     * @Route("/blog/archive", name="blog_archive")
     * @Route("/blog/archive/tag/{tag}", name="blog_archive_tag")
     * @Route("/blog/archive/{year}/{month}", name="blog_archive_filter", requirements={"year": "\d\d\d\d","month": "[1-9]|(1[0,1,2])"})
     */
    public function indexAction(Request $request, $tag='', $year='', $month ='')
    {
        $filterKey = self::FILTER_KEY;
        $pageParams = $this->getBases($request);

        $pageParams['active_tag'] = null;
        $route = $request->get('_route');

        if($route == 'blog_root_tag' || $route == 'blog_archive_tag'){
            $pageParams['active_tag'] = $this->getDoctrine()->getRepository('BlogBundle:Tag')->findOneBy(['enabled'=>1,'code'=>$tag]);
            if(!$pageParams['active_tag']){ //404
                return $this->redirectToRoute( $route == 'blog_root_tag'?'blog_root':'blog_archive' );
            }
        }

        $limit = 8;
        $page = 1;
        $filter = [];
        $specParams = [];

        $this->pageNavigationParams($request, $filter, $page, $limit);


        //main.breadcrumbs
        $breadCrumbs = $this->get('main.breadcrumbs')->add('blog.title',$this->generateUrl('blog_root'));
        $pageParams['breadcrumbs'] = &$breadCrumbs;

        //route params
        if($route == 'blog_root' || $route == 'blog_root_tag'){
            $filter[$filterKey]['archive'] = 0;
            $pageParams['page_title'] = 'blog.title';
            $pageParams['tag_path'] = 'blog_root_tag';
            $pageParams['post_path'] = 'blog_root_post';
        } else {
            $filter[$filterKey]['archive'] = 1;
            $pageParams['page_title'] = 'blog.archive';
            $pageParams['tag_path'] = 'blog_archive_tag';
            $pageParams['post_path'] = 'blog_archive_post';
            if($year && $month){
                //posts created in selected month
                $n = (intval($month));
                if($n<10)
                    $n = '0'.$n;
                $start = "'$year-$n'";

                if($month == 12){
                    $end = sprintf("'%d-01'", (intval($year)+1));
                } else {
                    $n = (intval($month)+1);
                    if($n<10)
                        $n = '0'.$n;
                    $end = sprintf("'%d-%s'",$year, $n);
                }
                $specParams['created'] = [
                    'AND' => [
                        "> $start",
                        "< $end"
                    ]
                ];
            }
            $breadCrumbs->add('blog.archive', $this->generateUrl('blog_archive'));
        }

        //filter
        $filter[$filterKey]['enabled'] = 1;
        $filter['_sort']['created'] = 'DESC';
        if($pageParams['active_tag']){
            $filter[$filterKey]['tags'] = $pageParams['active_tag']->getId();
        }

        // get objects
        $pageParams['objects'] = $this->customMatching($filterKey,'BlogBundle:Post',$filter,$specParams);
        $pageParams['tags'] = $this->getDoctrine()->getRepository('BlogBundle:Tag')->findBy(['enabled'=>1,'search'=>1],['pos'=>'ASC']);

        // archive info data
        $pageParams['archive'] = $this->getArchiveData();
        $pageParams['favorite'] = $this->getDoctrine()->getRepository('BlogBundle:Post')->findBy(['favorite'=>1,'enabled'=>1],['created'=>'DESC']);

        // page navigation
        $pager = $this->get('pager_generator');
        $pager->setParams($pageParams['objects']['meta']['total'] , $limit);
        $pageParams['pager'] =  $pager->getPager($page);

        return $this->render('MainBundle:Text:blog_root.html.twig',$pageParams);
    }

    /**
     * @Route("/blog/post/{post}", name="blog_root_post")
     * @Route("/blog/archive/post/{post}", name="blog_archive_post")
     */
    public function detailAction(Request $request, $post=''){
        $pageParams = $this->getBases($request);

        $pageParams['active_tag'] = null;
        $route = $request->get('_route');

        $pageParams['post'] = $this->getDoctrine()->getRepository('BlogBundle:Post')->findOneBy(['enabled'=>1,'code'=>$post]);
        if(!$pageParams['post']){ //404
            if($route == 'blog_root_post'){
                return $this->redirectToRoute('blog_root');
            } else {
                return $this->redirectToRoute('blog_archive');
            }
        }

        //main.breadcrumbs
        $breadCrumbs = $this->get('main.breadcrumbs')->add('blog.title',$this->generateUrl('blog_root'));
        $pageParams['breadcrumbs'] = &$breadCrumbs;

        $pageParams['tags'] = $this->getDoctrine()->getRepository('BlogBundle:Tag')->findBy(['enabled'=>1,'search'=>1],['pos'=>'ASC']);
        // archive info data
        $pageParams['archive'] = $this->getArchiveData();
        $pageParams['favorite'] = $this->getDoctrine()->getRepository('BlogBundle:Post')->findBy(['favorite'=>1,'enabled'=>1],['created'=>'DESC']);

        if($route == 'blog_archive_post'){
            $breadCrumbs->add('blog.archive', $this->generateUrl('blog_archive'));
            $breadCrumbs->add($pageParams['post']->getTitle(), $this->generateUrl('blog_archive_post',['post'=>$pageParams['post']->getCode()]));
            $pageParams['tag_path'] = 'blog_archive_tag';
            $pageParams['post_path'] = 'blog_archive_post';
        } else {
            $breadCrumbs->add($pageParams['post']->getTitle(), $this->generateUrl('blog_root_post',['post'=>$pageParams['post']->getCode()]));
            $pageParams['tag_path'] = 'blog_root_tag';
            $pageParams['post_path'] = 'blog_root_post';
        }

        return $this->render('MainBundle:Text:blog_root_post.html.twig',$pageParams);
    }

    public function getArchiveData(){
        $date = new \DateTime();

        $cacheKey = $date->format('Y-m-d');
        $cache = $this->get('cache');
        $cache->setNamespace(BlogBundle::CACHE_NAMESPACE);

        $resp = $cache->fetch($cacheKey);
        if($resp){
            return json_decode($resp,true);
        }elseif($resp === false) {
            $metaData = $this->getDoctrine()->getManager()->getClassMetadata(Post::class);
            $query =
                "SELECT 
                YEAR( `created` ) AS `year`, 
                MONTH( `created` ) AS `month`,
                COUNT( 1 ) AS `cnt`
            FROM  `" . $metaData->table['name'] . "`
            WHERE
                `archive`=1
            GROUP BY 
                `year` , 
                `month`
            ORDER BY 
                `year` DESC , 
                `month` DESC
            ";
            try {
                $stmt = $this->getDoctrine()->getManager()
                    ->getConnection()
                    ->prepare(
                        $query
                    );
                $stmt->execute();
                $ideas = $stmt->fetchAll();

                //cache update
                $cache->save($cacheKey,json_encode($ideas),86400);
                return $ideas;
            } catch (SyntaxErrorException $e) {

            }
        }
        return null;
    }
}
