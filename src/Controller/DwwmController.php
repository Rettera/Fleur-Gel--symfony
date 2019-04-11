<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use App\Repository\ArticleRepository;
use App\Entity\User;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Form\RegistrationFormType;
use App\Security\LoginFormAuthenticator;
use App\Form\ProfileType;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\Commentaire;

class DwwmController extends AbstractController
{

    /**
     * @Route("/", name="home")
     */
    public function home(Request $request)
    {
        $search = NULL;
        $formu = $this->createFormBuilder()
            ->add('search', SearchType::class)
            ->add('Rechercher', SubmitType::class)
            ->getForm();

            $formu->handleRequest($request);
            
            if ($formu->isSubmitted() && $formu->isValid()) {

                    $search = $formu['search']->getData();
                    $articles = $this->getDoctrine()
                        ->getRepository(Article::class)
                        ->findLike($search);

                return $this->render("dwwm/index.html.twig",[
                'articles' => $articles,
                ]);
                
            }
        return $this->render('dwwm/home.html.twig', [
            'controller_name' => 'DwwmController',
            'formu' => $formu->createView(),
        ]);
    }
     /**
     * @Route("/dwwm", name="Articles")

     */
    public function index()
    {
        $articles = $this->getDoctrine()
                        ->getRepository(Article::class)
                        ->orderByDate();

        
        return $this->render('dwwm/index.html.twig', [
            'controller_name' => 'DwwmController',
            'articles' => $articles
        ]);
    }
        /**
     * @Route("/dwwm/articles/{category}", name="category")
     */
    public function categF($category)
    {
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repository->FiltreCateg($category);
        return $this->render('dwwm/index.html.twig', [
            'controller_name' => 'DwwmController',
            'articles' => $articles
        ]);
    }
            /**
     * @Route("/dwwm/articles/{auteur}/filtre", name="auteur")
     */
    public function nomF($auteur)
    {
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repository->FiltreNom($auteur);
        return $this->render('dwwm/index.html.twig', [
            'controller_name' => 'DwwmController',
            'articles' => $articles
        ]);
    }    
    /**
     * @Route("/dwwm/new", name="New")
     */
    public function new( Request $request)
    {
        $article = new Article();
        
        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class)
            ->add('image', UrlType::class)
            ->add('content',  TextareaType::class)
            ->add('category', ChoiceType::class, ['choices'  => [
                    'Printemps' => 'Printemps',
                    'Ete' => 'Ete',
                    'Automne' => 'Automne',
                    'Hiver' => 'Hiver',
                    ],
            ])
            ->add('Envoyer', SubmitType::class)
            ->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $article->setCreatedAt(new \DateTime('', new \DateTimeZone('Europe/Paris')));

                    
                    $nom = $this-> getUser()->getPseudo();
                    $article->setAuteur($nom);
                    
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($article);
                    $em->flush();
                    $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
                    return $this->redirectToRoute('Articles');
                }
            }

             return $this->render('dwwm/new/new.html.twig', array(
            'form' => $form->createView(),
    ));
    }
        /**
     * @Route("/dwwm/perso/", name="Perso")
     */
    public function showPerso()
    {
        $user = $this-> getUser();//Recupere les info logger
        $nom = $this-> getUser()->getPseudo();
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repository->FiltreNom($nom);
        
        return $this->render('dwwm/perso.html.twig', [
            'controller_name' => 'DwwmController',
            'articles' => $articles,
            'user' => $user,
        ]);
    }
    /**
     * @Route("/dwwm/{id}", name="Article")
     */
    public function show($id, Request $request)
    {
        $repository = $this->getDoctrine()->getRepository(Article::class);
        $article = $repository->find($id);

        $comment = new Commentaire();
        
        $formul = $this->createFormBuilder($comment)
            ->add('contenu', TextareaType::class)
            ->add('Envoyer', SubmitType::class)
            ->getForm();
            $formul->handleRequest($request);
            if ($formul->isSubmitted()) {
                if ($formul->isValid()) {
                    $comment->setDate(new \DateTime('', new \DateTimeZone('Europe/Paris')));
                    $nom = $this-> getUser()->getPseudo();
                    $comment->setAuteur($nom);
                    $comment->setArticle($article);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($comment);
                    $em->flush();
                return $this->redirectToRoute('home');
                }
            }
        $repository = $this->getDoctrine()->getRepository(Commentaire::class);
        $commentus = $repository->orderByDates($id);

        return $this->render('dwwm/article/article.html.twig', [
            'controller_name' => 'DwwmController',
            'article' => $article,
            'formul' => $formul->createView(),
            'comments' => $commentus,
        ]);
    }
   /**
    * @Route ("/dwwm/{id}/del", name ="tr-del")
    *
    */
   
    public function deleteArticleAction($id, ArticleRepository $repo, Article $ad)
    {
        $em = $this->getDoctrine()->getManager();
       // $repo = $em->getRepository($repo);
       $ad = $repo->find($id);

        if($id <= 0)
        {
            throw $this->createNotFoundException('No for'.$id);
        }
        else 
        {
        $em->remove($ad);
        $em->flush();
        }

        return $this->redirectToRoute('Articles');
    }
    /**
     * @Route("/dwwm/update/{id}", name="update")
     */
    public function update($id, Request $request )
    {



        $repository = $this->getDoctrine()->getRepository(Article::class);
        $article = $repository->find($id);
        
        $form = $this->createFormBuilder($article)
            ->add('title', TextType::class)
            ->add('image', UrlType::class)
            ->add('content',  TextareaType::class)
            ->add('category', ChoiceType::class, ['choices'  => [
                    'Printemps' => 'Printemps',
                    'Ete' => 'Ete',
                    'Automne' => 'Automne',
                    'Hiver' => 'Hiver',
                    ],
            ])
            ->add('Envoyer', SubmitType::class)
            ->getForm();
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $article->setUpdatedAt(new \DateTime('', new \DateTimeZone('Europe/Paris')));
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($article);
                    $em->flush();
                    $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
                    return $this->redirectToRoute('Articles');
                }
            }

             return $this->render('dwwm/new/new.html.twig', array(
            'form' => $form->createView(),
    ));
    }

    /**
     * @Route("/forgotten_password", name="app_forgotten_password")
     */
    public function forgottenPassword(
        Request $request,
        UserPasswordEncoderInterface $encoder,
        \Swift_Mailer $mailer,
        TokenGeneratorInterface $tokenGenerator
    ): Response
    {

        if ($request->isMethod('POST')) {

            $email = $request->request->get('email');

            $entityManager = $this->getDoctrine()->getManager();
            $user = $entityManager->getRepository(User::class)->findOneByEmail($email);
            /* @var $user User */

            if ($user === null) {
                $this->addFlash('danger', 'Email Inconnu');
                return $this->redirectToRoute('homepage');
            }
            $token = $tokenGenerator->generateToken();

            try{
                $user->setResetToken($token);
                $entityManager->flush();
            } catch (\Exception $e) {
                $this->addFlash('warning', $e->getMessage());
                return $this->redirectToRoute('homepage');
            }

            $url = $this->generateUrl('app_reset_password', array('token' => $token), UrlGeneratorInterface::ABSOLUTE_URL);

            $message = (new \Swift_Message('Forgot Password'))
                ->setFrom('g.ponty@dev-web.io')
                ->setTo($user->getEmail())
                ->setBody(
                    "blablabla voici le token pour reseter votre mot de passe : " . $url,
                    'text/html'
                );

            $mailer->send($message);

            $this->addFlash('notice', 'Mail envoyé');

            return $this->redirectToRoute('home');
        }

        return $this->render('security/forgotten_password.html.twig');
    }
    /**
     * @Route("/reset_password/{token}", name="app_reset_password")
     */
    public function resetPassword(Request $request, string $token, UserPasswordEncoderInterface $passwordEncoder)
    {

        if ($request->isMethod('POST')) {
            $entityManager = $this->getDoctrine()->getManager();

            $user = $entityManager->getRepository(User::class)->findOneByResetToken($token);
            /* @var $user User */

            if ($user === null) {
                $this->addFlash('danger', 'Token Inconnu');
                return $this->redirectToRoute('home');
            }

            $user->setResetToken(null);
            $user->setPassword($passwordEncoder->encodePassword($user, $request->request->get('password')));
            $entityManager->flush();

            $this->addFlash('notice', 'Mot de passe mis à jour');

            return $this->redirectToRoute('home');
        }else {

            return $this->render('security/reset_password.html.twig', ['token' => $token]);
        }

    }
}
