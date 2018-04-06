<?php


namespace App\Controller;


use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use App\Service\ImageUploader;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller {
    const MAX_ITEM_PER_PAGE = 16;
    const MAX_TAG_PER_PAGE = 20;

    public function __construct() {

    }

    /**
     * @Route(path="/posts/{page}", name="post_list", defaults={"page" = 1}, requirements={"page"="\d+"})
     *
     * @param $page
     * @param Request $request
     * @param ImageRepository $imageRepository
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function listPosts($page, Request $request, ImageRepository $imageRepository) {
        $offset = ($page - 1) * self::MAX_ITEM_PER_PAGE;

        $getTag = $request->query->get('tags');
        if(isset($getTag)) {
            $tags = explode(' ', $getTag);
            $count = $imageRepository->countHasTags($tags);
            $images = $imageRepository->findByTags($tags, self::MAX_ITEM_PER_PAGE, $offset);
        } else {
            $count = $imageRepository->count([]);;
            $images = $imageRepository->findBy([], ['created' => 'DESC'], self::MAX_ITEM_PER_PAGE, $offset);
        }
        $nbImages = ($count == 0)? 1:$count;

        $imagesTags = [];
        foreach ($images as $image) {
            $imageTags = $image->getTags();
            foreach ($imageTags as $tag) {
                $title = $tag->getTitle();
                if(!in_array($title, $imagesTags)) {
                    $imagesTags[] = $title;
                }
            }
        }

        $pagination = [
            'nbPages' => ceil($nbImages / self::MAX_ITEM_PER_PAGE)
            , 'currentPage' => $page
            , 'routeName' => 'post_list'
            , 'range' => 5
            , 'routeParams' => ['tags'=>$getTag]
        ];

        return $this->render('post/list_posts.html.twig', [
            'images' => $images
            , 'pagination' => $pagination
            , 'selectedTags' => $getTag
            , 'imagesTags' => array_slice($imagesTags, 0, self::MAX_TAG_PER_PAGE)
        ]);
    }

    /**
     * @Route(path="/view/{id}", name="post_view")
     * @param $id
     * @param ImageRepository $imageRepository
     * @return Response
     */
    public function viewPost($id, ImageRepository $imageRepository) {
        $image = $imageRepository->findOneBy(['id' => $id]);

        return $this->render('post/view_post.html.twig', [
            'selectedTags' => ''
            , 'image' => $image
        ]);
    }

    /**
     * @Route(path="/upload", name="post_add")
     * @param Request $request
     * @param ImageUploader $uploader
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function addPosts(Request $request, ImageUploader $uploader) {
        $image = new Image();

        $form = $this->createForm(ImageType::class, $image);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $image->getImage();

            $paths = $uploader->upload($imageFile, true);
            $imagePath = $paths['image_path'];
            $thumbnailPath = $paths['thumbnail_path'];
            $imagePathRelative = $paths['image_path_relative'];
            $thumbnailPathRelative = $paths['thumbnail_path_relative'];
            $md5 = md5_file($imagePath);

            $image->setSize($imageFile->getClientSize());
            $image->setMd5($md5);
            $image->setImage($imagePathRelative);
            $image->setThumbnail($thumbnailPathRelative);
            $image->setAuthor($this->getUser());

            $em = $this->getDoctrine()->getManager();

            $tagsRepository =  $em->getRepository('App:Tag');
            $tags = [];
            foreach (explode(',', $image->getTags()) as $tag) {
                try {
                    $tags[] = $tagsRepository->findOneByTitleOrCreateIfNotExists($tag);
                } catch (ORMException $e) {
                    //TODO tag not added
                }
            }

            $image->setTags($tags);


            $em->persist($image);
            $em->flush();
        }

        return $this->render('post/add_posts.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(path="/image/delete/{id}", name="post_delete", requirements={"id"="\d+"}))
     * @Method("DELETE")
     * @param Image $image
     * @return Response
     */
    public function deletePost(Image $image) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $user = $this->getUser();
        if($user->getId() !== $image->getAuthor()->getId()) {
            return new Response(null, 403);
        }

        $fs = new Filesystem();

        $thumbnailFile = __DIR__.'/../../public'.$image->getThumbnail();
        $imageFile = __DIR__.'/../../public'.$image->getImage();

        $fs->remove([$imageFile, $thumbnailFile]);

        $em = $this->getDoctrine()->getManager();
        $em->remove($image);
        $em->flush();

        return new Response(null, 204);
    }



    /**
     * @Route(path="my-images", name="post_manage")
     *
     * @param ImageRepository $imageRepository
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function managePosts(ImageRepository $imageRepository) {
        $user = $this->getUser();
        $images = $imageRepository->findByAuthor($user->getId());
        return $this->render('post/manage_posts.html.twig', [
            'images' => $images,
        ]);
    }

    /**
     * @Route(path="/image/delete/all", name="post_delete_all")
     * @param ImageRepository $imageRepository
     * @return Response
     */
    public function deleteAllPosts(ImageRepository $imageRepository) {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $user = $this->getUser();
        $images = $imageRepository->findBy(['author' => $user]);

        $em = $this->getDoctrine()->getManager();
        $toDelete = [];

        foreach ($images as $image) {
            $toDelete[] = __DIR__.'/../../public'.$image->getThumbnail();
            $toDelete[] = __DIR__.'/../../public'.$image->getImage();
            $em->remove($image);
        }

        $fs = new Filesystem();
        try {
            $fs->remove($toDelete);
            $em->flush();
            dump($toDelete);
        } catch (IOException $ioe) {
            die($ioe->getMessage());
        }



        return new Response(null, 204);
    }


}