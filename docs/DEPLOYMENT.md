# Despliegue CI/CD (sin webhook)

Flujo: **push a `main` → GitHub construye la imagen → self-hosted runner en el
server hace el deploy**. El server nunca recibe conexiones entrantes; el runner
hace *long-poll* saliente a GitHub, así que **no se necesita webhook** ni abrir
puertos.

```
 push main
    │
    ├─ Job "build" (runner de GitHub, en la nube)
    │     docker build  →  push a ghcr.io  (imagen inmutable :SHA + :latest)
    │
    └─ Job "deploy" (self-hosted runner, DENTRO del server)
          docker compose pull      (baja la imagen ya construida)
          docker compose up -d      (recrea el contenedor)
          php artisan migrate --force
```

Una sola imagen monolítica corre **nginx + php-fpm + queue + scheduler** vía
supervisor. MySQL y Redis viven fuera (red externa `shared_network`).

---

## 1. Instalar el self-hosted runner en el server (una vez)

En GitHub: **repo → Settings → Actions → Runners → New self-hosted runner
(Linux x64)**. Copia el token que muestra y en el server:

```bash
sudo mkdir -p /opt/actions-runner && cd /opt/actions-runner
curl -o runner.tar.gz -L https://github.com/actions/runner/releases/latest/download/actions-runner-linux-x64.tar.gz
tar xzf runner.tar.gz

# Registrar (usa la URL y token que da GitHub)
./config.sh --url https://github.com/AlmexWebApps/Almex_maintenance_tracker_admin \
            --token <TOKEN_DE_GITHUB> \
            --labels self-hosted,linux \
            --unattended

# Instalar como servicio para que sobreviva reinicios
sudo ./svc.sh install
sudo ./svc.sh start
```

El usuario del runner debe poder usar Docker:

```bash
sudo usermod -aG docker $(whoami)   # o el usuario que corre el runner
# reiniciar el servicio del runner tras esto
```

Verifica: `docker compose version` debe responder como ese usuario.

---

## 2. Colocar el `.env` de producción en el server (una vez)

El `.env` **no** está en el repo ni en la imagen. Déjalo persistente, por ejemplo:

```bash
sudo mkdir -p /opt/almex
sudo nano /opt/almex/.env        # pega el .env de producción real
```

Debe incluir `APP_KEY`, credenciales de `DB_*`, `REDIS_*`, `APP_PORT`, etc.

---

## 3. Secrets del repositorio

**repo → Settings → Secrets and variables → Actions:**

| Secret            | Valor                                  |
|-------------------|----------------------------------------|
| `PROD_ENV_PATH`   | `/opt/almex/.env` (ruta al env del server) |

`GITHUB_TOKEN` es automático (no hay que crearlo). Da permiso de `packages:write`
para publicar en GHCR — ya está declarado en el workflow.

> La imagen en GHCR es privada por defecto. El runner hace `docker login ghcr.io`
> con `GITHUB_TOKEN` dentro del job, así que el pull funciona sin config extra.
> Si prefieres, puedes hacer el paquete público en GitHub Packages.

---

## 4. Primer despliegue

Haz push a `main` (o corre el workflow manual desde la pestaña Actions). El job
`build` publica la imagen y `deploy` la levanta en el server.

Rollback: en Actions, re-ejecuta un run anterior, **o** en el server:

```bash
APP_IMAGE=ghcr.io/almexwebapps/almex_maintenance_tracker_admin:<SHA_ANTERIOR> \
  docker compose -f docker-compose.portainer.yml --env-file /opt/almex/.env up -d
```

Cada imagen queda taggeada por `:SHA`, así que el rollback es inmediato.

---

## ¿Y Portainer?

Portainer sigue sirviendo para **ver/administrar** el stack (logs, consola,
estado). El deploy ya no depende de su webhook. Si en algún momento quieres que
Portainer también actualice solo, puede usar **polling de Git** (sin webhook),
pero eso reintroduce el build en el server — por eso el runner + GHCR es mejor.

## Notas

- `docker/php/Dockerfile.prod` es la imagen de producción (monolito).
- `docker/php/Dockerfile.dev` / `Dockerfile.portainer` quedan para desarrollo local.
- El caché de Laravel (`config/route/view/event:cache`) se genera en el
  `entrypoint` con el entorno real en cada arranque, no en build.
