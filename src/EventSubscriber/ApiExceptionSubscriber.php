<?php

namespace App\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly ?LoggerInterface $logger = null) {}

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onKernelException', 100]];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!str_contains($event->getRequest()->headers->get('Accept', ''), 'application/json')) {
            return;
        }

        $e = $event->getThrowable();

        $status  = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
        $headers = $e instanceof HttpExceptionInterface ? $e->getHeaders() : [];

        $message = $e->getMessage() ?: self::DEFAULT_MESSAGES[$status] ?? 'Server Error';

        if ($this->logger && $status >= 500) {
            $this->logger->error('API exception', [
                'status' => $status,
                'exception' => $e,
                'path' => $event->getRequest()->getPathInfo(),
            ]);
        }

        $event->setResponse(new JsonResponse(['message' => $message], $status, $headers));
    }

    private const array DEFAULT_MESSAGES = [
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        409 => 'Conflict',
        422 => 'Unprocessable Entity',
    ];
}