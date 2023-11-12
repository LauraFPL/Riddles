<?php
declare(strict_types=1);

namespace Salle\PuzzleMania\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Salle\PuzzleMania\Model\Image;
use Salle\PuzzleMania\Repository\ImageRepository;
use Salle\PuzzleMania\Repository\UserRepository;
use Ramsey\Uuid\Uuid;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Routing\RouteContext;

class ProfileController
{
    private Twig $twig;
    private UserRepository $userRepository;
    private ImageRepository $imageRepository;
    private Messages $flash;
    private const UPLOADS_DIR = __DIR__ . '/../../public/uploads/';
    private const IMAGE_READ_DIR = __DIR__ . '/../../../uploads/';
    private const JPEG_EXTENSION = 'image/jpeg';

    public function __construct( $twig, $flash,  $userRepository, $imageRepository){
        $this->twig = $twig;
        $this->flash = $flash;
        $this->userRepository = $userRepository;
        $this->imageRepository = $imageRepository;
    }

    public function showProfile(Request $request, Response $response): Response
    {
        $routeParser = RouteContext::fromRequest($request)->getRouteParser();

        $messages = $this->flash->getMessages();
        $uploadErrors = $messages['imageProfileError'] ?? [];

        $user = $this->userRepository->getUserById(intval($_SESSION['user_id']));
        $username = explode('@', $user->email())[0];

        $image = $this->imageRepository->getActualImageFromUser($_SESSION['user_id']) ?? [];
        $url = '';
        if(!empty($image)){
            $url = self::IMAGE_READ_DIR . $image->id();
        }


        return $this->twig->render($response, 'profile.twig', [
            'formAction' => $routeParser->urlFor('profileImage'),
            'uploadErrors' => $uploadErrors,
            'user' => $username,
            'email' => $user->email(),
            'img' => $url
        ]);
        
    }

    public function changeProfileImage(Request $request, Response $response): Response
    {
        $uuid = Uuid::uuid6();

        $file = $_FILES['imageFile'];
        $widthHeigh = getimagesize($file["tmp_name"]);
        $extension = '';

        $fileExtension = mime_content_type($file['tmp_name']);

        if($fileExtension == self::JPEG_EXTENSION){
            $extension = ".jpeg";
        }else{
            $extension = ".png";
        }

        //creem la carpeta uploads si no existeix 
        if (!file_exists(self::UPLOADS_DIR)) {
            mkdir(self::UPLOADS_DIR, 0777, true);
        }

        //movem el fitxer a la carpeta
        if (move_uploaded_file($file['tmp_name'], self::UPLOADS_DIR . $uuid->toString() . $extension)) {
            $image = new Image($uuid->toString().$extension, $file['name'], $widthHeigh[0], $widthHeigh[1], $_SESSION['user_id']);
            $this->imageRepository->createImageProfile($image);

        } else {
            $this->flash->addMessage("imageProfileError", "Error: There was a problem with the upload");
        }
        
        return $response->withHeader('Location','/profile')->withStatus(302);
        
    }
    
}
