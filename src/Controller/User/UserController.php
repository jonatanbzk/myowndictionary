<?php

namespace App\Controller\User;

use App\Controller\Mailer;
use App\Entity\Tag;
use App\Entity\User;
use App\Form\User\RegistrationFormType;
use App\Form\User\UpdateUserFormType;
use App\Form\User\UpdateUserPasswordFormType;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{

    private $session;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(SessionInterface $session,
                                EntityManagerInterface $manager)
    {
        $this->session = $session;
        $this->manager = $manager;
    }

    private function getTags($tagRepository)
    {
        $user = $this->getUser();
        $tags = $tagRepository->findBy(array('user' => $user));
        return $tags;
    }

    private function formError($form)
    {
        if ($form->isSubmitted() && count($form->getErrors(true)) !== 0) {
            $errors = $form->getErrors(true);
            $this->addFlash('danger', $errors);
        }
    }

    /**
     * @Route("/register", name="app_register")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param Mailer\Email $sendEmail
     * @return Response
     */
    public function register(Request $request, UserPasswordEncoderInterface
    $passwordEncoder, Mailer\Email $sendEmail): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setActivationCode(md5(rand()));

            $this->manager->persist($user);
            $this->manager->flush();

            $subject = 'Email verification';
            $view = 'emails/registration.html.twig';
            $name = $user->getUsername();
            $email = $user->getEmail();
            $link = 'https://mydictionary.org/activCode/' . $user->getId() .
                '/' . $user->getActivationCode();
            $sendEmail->index($subject, $name, $email, $view, $link);
            $this->addFlash('success', 'You have been 
            registered, please check your email');
        }
        $this->formError($form);

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/reset_password_form/{id}/{code}", name="reset_password_form")
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @return Response
     */
    public function resetPasswordForm(Request $request,
                                      UserPasswordEncoderInterface $passwordEncoder)
    {
        $form = $this->createForm(UpdateUserPasswordFormType::class);
        $form->handleRequest($request);

        $routeParameters = $request->attributes->get('_route_params');
        $id = $routeParameters['id'];
        $code = $routeParameters['code'];
        $repository = $this->getDoctrine()->getRepository(
            User::class);
        $user = $repository->find($id);
        if ($user->getActivationCode() !== $code) {
            $this->addFlash('danger', 'An error occurred, 
            please try again to update your password');
            return $this->redirectToRoute('app_login');
        }
        if ($form->isSubmitted() && $form->isValid() &&
            $user->getActivationCode() === $code) {
            if (!empty($form->get('plainPassword')->getData())) {
                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }
            $this->manager->flush();

            $this->addFlash('success', 'You updated your 
            password successfully.');
            return $this->redirectToRoute('app_login');
        }
        $this->formError($form);

        return $this->render('resetpassword/passwordnew_form.html.twig', [
            'user' => $user,
            'updateUserForm' => $form->createView()
        ]);
    }

    /**
     * @Route("/settings/{id}", name="user_update", methods="GET|POST")
     * @param User $user
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param TagRepository $tagRepository
     * @return RedirectResponse
     */
    public function update(User $user, Request $request,
                           UserPasswordEncoderInterface $passwordEncoder,
                           TagRepository $tagRepository): Response
    {
        $form = $this->createForm(UpdateUserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!empty($form->get('plainPassword')->getData())) {
                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );
            }
            $this->manager->flush();

            $this->addFlash('success', 'You updated your account
             successfully.');

            return $this->redirectToRoute('homepage_index');
        }
        $this->formError($form);
        return $this->render('settingspage/settingspage.html.twig', [
            'user' => $user,
            'updateUserForm' => $form->createView(),
            'tags' => $this->getTags($tagRepository)
        ]);
    }

    /**
     * @Route("/activCode/{id}/{code}", name="activCode")
     * @param Request $request
     * @return Response
     */
    public function emailValidation(Request $request): Response
    {
        $routeParameters = $request->attributes->get('_route_params');
        $id = $routeParameters['id'];
        $code = $routeParameters['code'];

        $repository = $this->getDoctrine()->getRepository(
            User::class);
        $user = $repository->find($id);

        if ($user != null and $user->getId() == $id and
            $user->getActivationCode() == $code) {
            $user->setEmailValid(true);
            $this->manager->flush();
            $this->addFlash('success', 'Your email is now validated');

        } else {
            $this->addFlash('danger', 'You don\'t have any account');
        }
        return $this->redirectToRoute('app_login');
    }


    /**
     * @Route("/settings/deleteTag/{id}", name="tag_delete", methods="DELETE")
     * @param Request $request
     * @param Tag $tag
     * @return RedirectResponse
     */
    public function deleteTag(Request $request, Tag $tag)
    {
        $tagId = (int) $tag->getId();
        if ($tagId != 0 && $this->isCsrfTokenValid
        ('delete' . $tagId, $request->get('_token'))) {

            $this->manager->remove($tag);
            $this->manager->flush();

            $urlTag = $request->get('id');
            $sessionTag = $this->session->get('current_tag');
            if (!empty($sessionTag) && $sessionTag[0] == $urlTag) {
                $this->session->remove('current_tag');
            }
            $this->addFlash('success', 'Your dictionary has
            been deleted');
        }
         return $this->redirectToRoute('homepage_index');
    }

    /**
     * @Route("/settings/delete/{id}", name="user_delete", methods="DELETE")
     * @param User $user
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteUser(User $user, Request $request)
    {
        $userId = (int) $user->getId();
        if ($userId != 0 && $this->isCsrfTokenValid
        ('delete' . $userId, $request->get('_token'))) {

            $this->manager->remove($user);
            $this->manager->flush();

            // necessary to redirect
            $this->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();
        }
        return $this->redirectToRoute('app_logout');
    }

}
