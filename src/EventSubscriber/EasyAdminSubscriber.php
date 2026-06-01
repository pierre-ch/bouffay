<?php

namespace App\EventSubscriber;

use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private Security $security,
        #[Autowire('%kernel.logs_dir%')] private string $logsDir
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => ['onAfterEntityPersisted'],
            AfterEntityUpdatedEvent::class => ['onAfterEntityUpdated'],
            AfterEntityDeletedEvent::class => ['onAfterEntityDeleted'],
        ];
    }

    public function onAfterEntityPersisted(AfterEntityPersistedEvent $event): void
    {
        $this->logAction('Création', $event->getEntityInstance());
    }

    public function onAfterEntityUpdated(AfterEntityUpdatedEvent $event): void
    {
        $this->logAction('Modification', $event->getEntityInstance());
    }

    public function onAfterEntityDeleted(AfterEntityDeletedEvent $event): void
    {
        $this->logAction('Suppression', $event->getEntityInstance());
    }

    private function logAction(string $action, object $entity): void
    {
        $user = $this->security->getUser();
        $username = $user ? $user->getUserIdentifier() : 'Anonyme';
        $date = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        
        $message = sprintf("[%s] INFO: [EasyAdmin] %s de %s (ID: %s) par %s\n",
            $date,
            $action,
            $this->getEntityShortName($entity),
            $this->getEntityId($entity),
            $username
        );

        file_put_contents($this->logsDir . '/admin.log', $message, FILE_APPEND);
    }

    private function getEntityShortName(object $entity): string
    {
        $reflection = new \ReflectionClass($entity);
        return $reflection->getShortName();
    }

    private function getEntityId(object $entity): string
    {
        return method_exists($entity, 'getId') ? (string) $entity->getId() : '?';
    }
}
