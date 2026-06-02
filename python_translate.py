import re

with open('translations/messages.pt.yaml', 'r') as f:
    content = f.read()

replacements = {
    'nav.home: Accueil': 'nav.home: Início',
    'nav.products: Produits': 'nav.products: Produtos',
    'nav.cart: Panier': 'nav.cart: Carrinho',
    'nav.login: Connexion': 'nav.login: Entrar',
    'nav.register: Inscription': 'nav.register: Cadastrar',
    'nav.logout: Déconnexion': 'nav.logout: Sair',
    'nav.my_account: \'Mon compte\'': 'nav.my_account: \'Minha Conta\'',
    'nav.my_orders: \'Mes commandes\'': 'nav.my_orders: \'Meus Pedidos\'',
    'nav.admin: Administration': 'nav.admin: Administração',
    'nav.language: Langue': 'nav.language: Idioma',
    'title.home: \'Accueil — Bouffay\'': 'title.home: \'Início — Bouffay\'',
    'title.products: \'Annonces — Bouffay\'': 'title.products: \'Produtos — Bouffay\'',
    'title.cart: \'Mon Panier - Bouffay Shop\'': 'title.cart: \'Meu Carrinho - Bouffay Shop\'',
    'title.login: \'Connexion — Bouffay\'': 'title.login: \'Entrar — Bouffay\'',
    'title.register: \'Inscription — Bouffay\'': 'title.register: \'Cadastrar — Bouffay\'',
    'section.welcome: \'Bienvenue sur Bouffay\'': 'section.welcome: \'Bem-vindo ao Bouffay\'',
    'section.discover: \'Découvrez des snacks du monde entier.\'': 'section.discover: \'Descubra snacks do mundo inteiro.\'',
    'form.locale_fr: Français': 'form.locale_fr: Francês',
    'form.locale_en: English': 'form.locale_en: Inglês',
    'form.locale_pt: \'Português (BR)\'': 'form.locale_pt: \'Português (BR)\'',
}

for fr, pt in replacements.items():
    content = content.replace(fr, pt)

with open('translations/messages.pt.yaml', 'w') as f:
    f.write(content)
