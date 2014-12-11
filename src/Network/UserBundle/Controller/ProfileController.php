<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Network\UserBundle\Controller;

use FOS\UserBundle\Controller\ProfileController as BaseController;
use FOS\UserBundle\Model\UserInterface;
use Network\StoreBundle\DBAL\RelationshipStatusEnumType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Network\StoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class ProfileController extends BaseController
{
    /**
     * Show the user
     */
    public function showAction()
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->redirect($this->generateUrl('user_profile', ['id' => $user->getId()]));

//        return $this->render('FOSUserBundle:Profile:show.html.twig', array(
//            'user' => $user
//        ));
    }

    public function profileAction($id, Request $request)
    {
        $user = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));

        $is_cur_user = false;
        $fsStatus = RelationshipStatusEnumType::FS_NONE;
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $cur_user = $this->getUser();
            $is_cur_user = ($cur_user->getId() === $user->getId());
            $fsStatus = $cur_user->getRelationshipStatus($id);
        }

        return $this->render('NetworkUserBundle:Profile:show.html.twig', [
            'user' => $user,
            'rl_status' => $fsStatus,
            'friends' => $user->getRelationshipsWithStatus(RelationshipStatusEnumType::FS_ACCEPTED),
            'subscribers' => $user->getRelationshipsWithStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER),
            'subscribed_on' => $user->getRelationshipsWithStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME),
            'is_cur_user' => $is_cur_user
        ]);
    }

    public function showFriendsAction($id)
    {
        $user = $this->getDoctrine()->getRepository('NetworkStoreBundle:User')->find($id);
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));

        return $this->render('NetworkUserBundle:Profile:friends.html.twig', [
            'user_id' => $user->getId(),
            'friends' => $user->getRelationshipsWithStatus(RelationshipStatusEnumType::FS_ACCEPTED),
            'subscribers' => $user->getRelationshipsWithStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER),
            'subscribed_on' => $user->getRelationshipsWithStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_ME)
        ]);
    }

    public function showProfileFriendsAction()
    {
        $user = $this->getUser();
        if (empty($user)) return $this->redirect($this->generateUrl('mainpage'));
        return $this->showFriendsAction($user->getId());
    }

    public function manageFriendshipRequestsAction()
    {
        return $this->render('NetworkUserBundle:Profile:manage_requests.html.twig', [
            'friendship_requests' => $this->getUser()->getRelationshipsWithStatus(RelationshipStatusEnumType::FS_SUBSCRIBED_BY_USER, true)
        ]);
    }

}
