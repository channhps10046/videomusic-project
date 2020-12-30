<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\Video;
use App\Repository\VideoRepository;
use App\Utils\CategoryTreeFrontPage;
use DateTime;
use phpDocumentor\Reflection\Types\This;
use Symfony\Component\HttpFoundation\Request;

class FrontController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        $videos = $this->getDoctrine()->getRepository(Video::class)->findAll();

        return $this->render('front/index.html.twig', [
            'videos' => $videos
        ]);
    }

    /**
     * @Route("/index", name="index_user")
     */
    public function indexUser(): Response
    {
        $videos = $this->getDoctrine()->getRepository(Video::class)->findAll();

        return $this->render('front/index.html.twig', [
            'videos' => $videos
        ]);
    }

    public function mainCategories()
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findBy([], ['id' => 'ASC']);

        return $this->render('front/_main_categories.html.twig', [
            'categories' => $categories
        ]);
    }


    /**
     * @Route("/video-list/category/{categoryname},{id}", name="video_list")
     */
    public function videoList($id, CategoryTreeFrontPage $categories)
    {

        $ids = $categories->getChildIds($id);
        array_push($ids, $id);
        $videos = $this->getDoctrine()
            ->getRepository(Video::class)
            ->findByChildIds($ids);

        return $this->render('front/index.html.twig', [
            'videos' => $videos
        ]);
    }


    /**
     * @Route("/video-details/{video}", name="video_details")
     */

    public function videoDetails(VideoRepository $repo, $video)
    {
        $videos = $this->getDoctrine()->getRepository(Video::class)->findAll();
        return $this->render('front/video_details.html.twig', [
            'video' => $repo->videoDetails($video),
            'videos' => $videos
        ]);
    }

    /**
     *@Route("/serach", name="search_result", methods={"GET"})
     */

    public function SearchResult(Request $request)
    {
        $videos = null;
        $query = null;

        if ($query = $request->get('query')) {
            $videos = $this->getDoctrine()->getRepository(Video::class)->findByTitle($query);
        }

        return $this->render('front/index.html.twig', [
            'videos' => $videos,
        ]);
    }

    /**
     *@Route("/new-comment/{video}", name="comments", methods={"POST"})
     */

    public function newComment(Video $video, Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (!empty(trim($request->request->get('comment')))) {
            $comment = new Comment();
            $comment->setContent($request->request->get('comment'));
            $comment->setCreatedAt(new DateTime());
            $comment->setUser($this->getUser());
            $comment->setVideo($video);


            $em =  $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
        }

        return $this->redirectToRoute('video_details', [
            'video' => $video->getId()
        ]);
    }
}
