<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Log;


class Handler extends ExceptionHandler
{
    private $STATUS = 'status';
    private $CODE = 'code';
    private $MESSAGE = 'message';
    private $RESULT = 'result';
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if($request->header('token') !="" && $request->header('apikey') !="" && $request->wantsJson()){

            return $this->handleApiException($request, $exception);
        }
        if ($exception instanceof \Illuminate\Session\TokenMismatchException) {
            return redirect('/');
        }

        if ($exception instanceof \Spatie\Permission\Exceptions\UnauthorizedException) {
            return redirect('/admin/error-403');
        }

        if ($this->shouldReport($exception)) {
            Log::error('CatchError',[
                'errors' =>$exception,
                'path' => $request->path(),
                'query' => $request->query(),
                'request' => $request->except(['password','token','apikey','email']),
                'file' => $request->file(),
                'ip' => $request->ip(),
            ]);
        }

        return parent::render($request, $exception);
    }

    private function handleApiException($request, Exception $exception)
    {
        $exception = $this->prepareException($exception);

        if ($exception instanceof \Illuminate\Http\Exception\HttpResponseException) {
            $exception = $exception->getResponse();
        }

        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            $exception = $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            $exception = $this->convertValidationExceptionToResponse($exception, $request);
        }

        return $this->customApiResponse($exception);
    }

    private function customApiResponse($exception)
    {
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        } else {
            $statusCode = 500;
        }

        $return = array($this->STATUS=>false,$this->CODE=>401,$this->MESSAGE=>"ANDA TIDAK TERAUNTENTIKASI",$this->RESULT=>null);

        switch ($statusCode) {
            case 401:
                $return[$this->MESSAGE] = 'Unauthorized';
                break;
            case 403:
                $return[$this->MESSAGE] = 'Forbidden';
                break;
            case 404:
                $return[$this->MESSAGE] = 'Not Found';
                break;
            case 405:
                $return[$this->MESSAGE] = 'Method Not Allowed';
                break;
            case 422:
                $return[$this->MESSAGE] = $exception->original['message'];
                $return['errors'] = $exception->original['errors'];
                break;
            default:
                $return[$this->MESSAGE] = ($statusCode == 500) ? 'Whoops, looks like something went wrong' : $exception->getMessage();
                break;
        }

        if (config('app.debug')) {
            $return['trace'] = $exception->getTrace();
        }

        $return[$this->CODE] = $statusCode;

        
        return response()->json($return,$return[$this->CODE]);
    }
}
