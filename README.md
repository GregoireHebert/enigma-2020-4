# Enigma 2020 - 4ème année

## Install

```sh
symfony composer install
symfony console doctine:schema:create
yarn install
yarn encore dev
```

## Lancer le projet

```sh
symfony serve
```

Ouvrir https://127.0.0.1:8000

## Commande matchMaking des joueurs en attente dans le lobby

```sh
symfony console app:match:creates
```

## compile front assets dev

```sh
yarn encore dev --watch
```

## compile front assets prod

```sh
yarn encore production
```
