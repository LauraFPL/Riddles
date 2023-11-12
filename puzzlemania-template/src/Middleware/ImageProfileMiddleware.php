<?php
//We use this instead of the checking if the user is logged in every single controller.
//To avoid using that function in every call of our application
//We create a middleware that will start the session for us.
namespace Salle\PuzzleMania\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Flash\Messages;
use Slim\Psr7\Response;
use Slim\Routing\RouteContext;

final class ImageProfileMiddleware
{
    // We use this const to define the extensions that we are going to allow
    private const ALLOWED_CONTENT_TYPE = ['image/jpeg', 'image/png', 'image/jpg'];
    private const MEGABIT = 1048576;


    public function __construct(private Messages $flash)
    {
    }

    public function __invoke(Request $request, RequestHandler $next): Response
    {

        if (empty($_FILES)) {
            
            $this->flash->addMessage("imageProfileError", "Error: No file uploaded!");
            $response = new Response();
            return $response->withHeader('Location','/profile')->withStatus(302);

        }else{

            $file = $_FILES["imageFile"];


            //Comprovem si ha hagut un error________________________________________________________________ 
            if ($file["error"] !== UPLOAD_ERR_OK) {  
                $this->flash->addMessage("imageProfileError", "Error: There was a problem with the file");
                $response = new Response();
                return $response->withHeader('Location','/profile')->withStatus(302);

            }


            //Comprovem si és el tamany del fitxer és menor a un 1mb________________________________________ 
            if ($file["size"] > SELF::MEGABIT){
                $this->flash->addMessage("imageProfileError", "Error: The maximum size for the file is 1mb");
                $response = new Response();
                return $response->withHeader('Location','/profile')->withStatus(302);

            }


            //Comprovem si es PNG o JPEG_____________________________________________________________________
                //https://techlister.com/php/check-if-the-uploaded-file-is-an-image-in-php/
            if (!in_array(mime_content_type($file['tmp_name']), self::ALLOWED_CONTENT_TYPE, true)) {
                $this->flash->addMessage("imageProfileError", "Error: Uploaded a not able file, just png and jpg");
                $response = new Response();
                return $response->withHeader('Location','/profile')->withStatus(302);
            }


            //Comprovem si la imatge és de dimensions igual o menor de 400 pixes_____________________________
            $widthHeight = getimagesize($file["tmp_name"]);
            if($widthHeight[0] > 400 || $widthHeight[1] > 400 ){
                $this->flash->addMessage("imageProfileError", "Error: The width or height are higher than 400 pixels");
                $response = new Response();
                return $response->withHeader('Location','/profile')->withStatus(302);
            }


            return $next->handle($request);
        }

       
    }

}

