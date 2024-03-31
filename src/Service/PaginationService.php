<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class PaginationService{

    /**
     * Le nom de l'entité sur laquelle on veut effectuer une pagination
     *
     * @var string
     */
    private string $entityClass;
    
    /**
     * Le nombre d'enregistrement à récupérer
     *
     * @var integer
     */
    private int $limit = 10;

    /**
     * La page courante
     *
     * @var integer
     */
    private int $currentPage = 1;

    /**
     * Le manager de Doctrine qui nous permet notament de trouver le repository 
     *
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $manager;


    /**
     * Le moteur de template Twig qui va permettre de générer le rendu de la pagination
     *
     * @var Twig\Environment
     */
    private Environment $twig;

    /**
     * Le nom de la route que l'on veut utiliser pourles boutons de navigations
     *
     * @var string
     */
    private $route;

    /**
     * Le chemin vers le template qui contient la pagination
     *
     * @var string
     */
    private $templatePath;

    private ?array $order = null;

    /**
     * Constructeur du service de pagination qui sera appelé par Symfony
     * 
     * N'oubliez pas de configurer votre fichier service.yaml afin que symfony sache quelle valeur utiliser 
     * pour le $templatePath
     *
     * @param EntityManagerInterface $manager
     * @param Environment $twig
     * @param RequestStack $request
     * @param string $templatePath
     */
    public function __construct(EntityManagerInterface $manager, Environment $twig, RequestStack $request, string $templatePath)
    {
        $this->route = $request->getCurrentRequest()->attributes->get('_route');
        $this->manager = $manager;
        $this->twig = $twig;
        $this->templatePath = $templatePath;
    }

    /**
     * Permet de spécifier l'entité sur laquelle on souhaite paginer
     *
     * @param string $entityClass
     * @return self
     */
    public function setEntityClass(string $entityClass): self
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * Permet de récupérer l'entité sur laquelle on est en train de paginer
     *
     * @return string
     */
    public function getEntityClass(): string{
        return $this->entityClass;
    }

    /**
     * Permet de spécifier le nombre d'enregistrament que l'on souhaite obtenir
     *
     * @param int $limit
     * @return self
     */
    public function setLimit(int $limit): self{
        $this->limit = $limit;
        return $this;
    }

    /**
     * Permet de récupérer le nombre d'enregistrement qui seront renvoyés
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Permet de spécifier l'ordre que l'on souhaite afficher pour les résultats
     *
     * @param array $myOrder
     * @return self
     */
    public function setOrder(array $myOrder): self
    {
        $this->order = $myOrder;
        return $this;
    }

    /**
     * Permet de récupérer le tableau des order
     *
     * @return array
     */
    public function getOrder(): array
    {
        return $this->order;
    }

    /**
     * Permet de spécifier la page que l'on souhaite afficher
     *
     * @param int $page
     * @return self
     */ 
    public function setPage(int $page): self{
        $this->currentPage = $page;
        return $this;
    }

    /**
     * Permet de récupérer la page qui est actuellement affichée
     *
     * @return int
     */
    public function getPage(): int
    {
        return $this->currentPage;
    }

    /**
     * Permet de récupérer les données paginées pour une entité spécifique
     * @throws Exception si la propriété $entityClass n'est pas définie
     * @return array
     */
    public function getData(): array
    {
        if(empty($this->entityClass))
        {
            throw new \Exception("Vous n'avez pas spécifié l'entité sur laquelle nous devons paginer! Utilisez la méthode setEntityClass() de votre objet PaginationService");
        }

        // calculer l'offset
        $offset = $this->currentPage * $this->limit - $this->limit;
        // demander au repository de trouver les éléments 
        // $repo = $this->manager->getRepository($this->entityClass);
        // $data = $repo->findBy([],[],$this->limit,$offset);

        // // renvoyer les données
        // return $data;
            // Définir l'ordre par défaut si aucun n'a été spécifié
        if ($this->order === null) {
            $this->order = ['id' => 'DESC'];
        }


        return $this->manager
                        ->getRepository($this->entityClass)
                        ->findBy([],$this->order,$this->limit,$offset);
    }


    /**
     * Permet de récupérer le nombre de page qui existent sur une entité particulière
     *
     * @throws Exception si la propriété $entityClass n'est pas configurée
     * @return integer
     */
    public function getPages(): int
    {
        if(empty($this->entityClass))
        {
            throw new \Exception("Vous n'avez pas spécifié l'entité sur laquelle nous devons paginer! Utilisez la méthode setEntityClass() de votre objet PaginationService");
        }
        // conntaire le total des enregirstement de la tablez
        // $repo = $this->manager->getRepository($this->entityClass);
        // $total = count($repo->findAll());
        $total = count($this->manager
                        ->getRepository($this->entityClass)
                        ->findAll());
  
        return ceil($total / $this->limit);;
    }

    /**
     * Permet d'afficher le rendu de la navigation au sein d'un template Twig
     * On se sert ici de notre moteur de rendu afin de compiler le template qui se trouve au chemin de notre propriété
     * $templatePath, en lui passant les variables page, pages et route 
     *
     * @return void
     */
    public function display(): void
    {
        $this->twig->display($this->templatePath, [
            'page' => $this->currentPage,
            'pages' => $this->getPages(),
            'route'=>$this->route
        ]);
    }

    /**
     * Permet de choisir un template de pagination
     *
     * @param string $templatePath
     * @return self
     */
    public function setTemplatePath(string $templatePath): self {
        $this->templatePath = $templatePath;

        return $this;
    }

    /**
     * Permet de récupérer le templatePath actuellement utilisé
     *
     * @return string
     */
    public function getTemplatePath(): string{
        return $this->templatePath;
    }

    /**
     * Permet de changer la route par défaut pour les liens de la navigation
     *
     * @param string $route
     * @return self
     */
    public function setRoute(string $route): self{
        $this->route = $route;

        return $this;
    }

    /**
     * Permet de récupérer le nom de la route qui sera utilisée sur les liens de la pagination
     *
     * @return string
     */
    public function getRoute(): string {
        return $this->route;
    }

}