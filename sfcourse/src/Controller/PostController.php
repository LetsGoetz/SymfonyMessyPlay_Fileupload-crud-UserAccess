<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Entity\Post;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Form\PostType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @Route("/post", name="post.")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(PostRepository $postRepository)
    {
        $posts = $postRepository->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request)
    {
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em = $this->getDoctrine()->getManager();
            /** @var UploadedFile $file  */
            $file = $request->files->get('post')['attachment'];

            if ($file) {
                $filename = md5(uniqid()) . '.' . $file->guessExtension();

                $file->move($this->getParameter('uploads_dir'), $filename);

                $post->setImage($filename);
            }

            $em->persist($post);
            $em->flush();

            return $this->redirect($this->generateUrl('post.index'));
        }

        return $this->render('post/create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/show/{id}", name="show")
     */
    public function show(Post $post /* $id, PostRepository $postRepository */)
    {
        // $post = $postRepository->findPostByCategory($id);

        return $this->render('post/show.html.twig', ['post' => $post]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     *
     */
    public function remove(Post $post)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'Post was deleted');

        return $this->redirect($this->generateUrl('post.index'));
    }
}
