<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\User;
use App\Entity\Video;
use App\Form\CategoryType;
use App\Form\VideoType;
use App\Form\UserType;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Repository\VideoRepository;
use App\Utils\Interfaces\UploaderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class AdminController extends AbstractController
{


    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return $this->render('admin/base.html.twig');
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function dashBoard(CategoryRepository $cate, UserRepository $user, VideoRepository $video, CommentRepository $comment)
    {
        $categories = $cate->findAll();
        $users = $user->findAll();
        $videos = $video->findAll();
        $comments = $comment->findAll();

        $catName = [];
        $catCount = [];
        $catCountNumber = [];

        $userName = [];
        $userCount = [];
        $userCountNumber = [];

        $videoTitle = [];
        $videoCount = [];
        $videoCountNumber = [];


        $commentCount = [];
        $commentCountNumber = [];


        foreach ($categories as $categorie) {
            $catName[] = $categorie->getName();
            $catCountNumber[] = count(array(($categorie->getId())));
            $catCount = array_sum($catCountNumber);
        }

        foreach ($users as $user_count) {
            $userName[] = $user_count->getName();
            $userCountNumber[] = count(array(($user_count->getId())));
            $userCount = array_sum($userCountNumber);
        }

        foreach ($videos as $video_count) {
            $videoTitle[] = $video_count->getTitle();
            $videoCountNumber[] = count(array(($video_count->getId())));
            $videoCount = array_sum($videoCountNumber);
        }

        foreach ($comments as $comment_count) {
            $commentCountNumber[] = count(array(($comment_count->getId())));
            $commentCount = array_sum($commentCountNumber);
        }

        return $this->render('admin/dashboard.html.twig', [
            'catCount' => json_encode($catCount),
            'userCount' => json_encode($userCount),
            'videoCount' => json_encode($videoCount),
            'commentCount' => json_encode($commentCount)
        ]);
    }



    /**
     * @Route("/category", name="category",  methods={"GET","POST"})
     */
    public function showCategory(Request $request)
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $is_invalid = null;

        if ($this->savecategory($category, $form, $request)) {
            return $this->redirectToRoute('category');
        } elseif ($request->isMethod('post')) {
            $is_invalid = ' is-invalid';
        }
        return $this->render('admin/category.html.twig', [
            'categories' => $categories,
            'form' => $form->createView(),
            'is_invalid' => $is_invalid
        ]);
    }

    /**
     * @Route("/edit-category/{id}", name="edit_category", methods={"GET","POST"})
     */
    public function editCategory(Request $request, $id)
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $entityManager = $this->getDoctrine()->getManager();

        $category = $entityManager->getRepository(Category::class)->find($id);

        // $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $is_invalid = null;

        if ($this->savecategory($category, $form, $request)) {
            return $this->redirectToRoute('category');
        } elseif ($request->isMethod('post')) {
            $is_invalid = ' is-invalid';
        }
        return $this->render('admin/edit_category.html.twig', [
            'categories' => $categories,
            'form' => $form->createView(),
            'is_invalid' => $is_invalid
        ]);
    }

    /**
     * @Route("/delete-category/{id}", name="delete_category")
     */
    public function deleteCategory(Category $category)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        return $this->redirectToRoute('category');
    }

    public function savecategory($category, $form, $request)
    {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $category->setName($request->request->get('category')['name']);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();

            return true;
        }
        return false;
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $helper)
    {
        return $this->render('front/login.html.twig', [
            'error' => $helper->getLastAuthenticationError()
        ]);
    }

    /**
     * @Route("/register", name="register")
     */
    public function register(UserPasswordEncoderInterface $password_encoder, Request $request, SessionInterface $session)
    {
        $user = new User;
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $user->setName($request->request->get('user')['name']);
            $user->setLastName($request->request->get('user')['last_name']);
            $user->setEmail($request->request->get('user')['email']);
            $password = $password_encoder->encodePassword($user, $request->request->get('user')['password']['first']);
            $user->setPassword($password);
            $user->setRoles(['ROLE_USER']);

            $entityManager->persist($user);
            $entityManager->flush();

            $this->loginUserAutomatically($user, $password);

            return $this->redirectToRoute('index');
        }
        return $this->render('front/register.html.twig', ['form' => $form->createView()]);
    }

    private function loginUserAutomatically($user, $password)
    {
        $token = new UsernamePasswordToken(
            $user,
            $password,
            'main', // security.yaml
            $user->getRoles()
        );
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logout(): void
    {
        throw new \Exception('This should never be reached!');
        session_destroy();
    }



    /**
     *@Route("/videos", name="videos")
     */

    public function videos()
    {
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();
        $videos = $this->getDoctrine()->getRepository(Video::class)->findBy([], ['title' => 'ASC']);
        return $this->render('admin/videos.html.twig', [
            'videos' => $videos,
            'categories' => $categories
        ]);
    }


    /**
     *@Route("/update-video-category/{video}", name="update_video_category", methods={"POST"})
     */

    public function updateVideoCategory(Request $request, Video $video)
    {
        $em = $this->getDoctrine()->getManager();

        $category = $this->getDoctrine()->getRepository(Category::class)->find($request->request->get('video_category'));

        $video->setCategory($category);

        $em->persist($video);
        $em->flush();

        return $this->redirectToRoute('videos');
    }

    /**
     *@Route("/delete-video/{video}", name="delete_video",)
     */

    public function deleteVideo(Video $video)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($video);
        $em->flush();

        return $this->redirectToRoute('videos');
    }


    /**
     *@Route("/upload-video", name="upload_video",)
     */

    public function uploadVideo(Request $request, UploaderInterface $fileUploader)
    {
        $video = new Video();
        $form = $this->createForm(VideoType::class, $video);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $file = $video->getUploadedVideo();
            $fileName = $fileUploader->upload($file);

            $base_path = Video::uploadFolder;
            $video->setPath($base_path . $fileName[0]);
            $video->setTitle($fileName[1]);

            $em->persist($video);
            $em->flush();

            return $this->redirectToRoute('videos');
        }

        return $this->render('admin/upload_video_locally.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     *@Route("/user", name="users")
     */
    public function UserList()
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }

    /**
     *@Route("/delete-user/{id}", name="delete_user")
     */
    public function deleteUser(User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('users');
    }

    /**
     *@Route("/comments", name="comments_admin")
     */
    public function comments()
    {
        $users = $this->getDoctrine()->getRepository(User::class)->findAll();
        $comments = $this->getDoctrine()->getRepository(Comment::class)->findAll();

        return $this->render('admin/comments.html.twig', [
            'comments' => $comments,
            'users' => $users
        ]);
    }

    /**
     *@Route("/delete-comment/{comment}", name="delete_comments")
     */
    public function deleteComments(Comment $comment)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($comment);
        $em->flush();


        return $this->redirectToRoute('comments_admin');
    }
}
