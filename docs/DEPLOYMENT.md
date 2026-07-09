# Despliegue CI/CD (sin webhook)

Flujo: **push a `dev` o `main` → GitHub construye la imagen → self-hosted runner
en el server hace el deploy**. El server nunca recibe conexiones entrantes; el
runner hace *long-poll* saliente a GitHub, así que **no se necesita webhook** ni
abrir puertos. Toda la config (incluido el `.env`) vive en el repo/secrets: para
cambiar algo solo tocas el repo, nunca el server.

- Iteras en la rama **`dev`** → cada push despliega para probar.
- Cuando quede estable, **merge a `main`** → despliega la versión estable.
- Ambas ramas usan el mismo `.env` (secreto `PROD_ENV_FILE`).

```
 push dev / main
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

## 2. Cargar el `.env` de producción como secreto (una vez)

El `.env` **no** está en el repo ni en la imagen: vive como secreto de GitHub.
El action lo escribe en un archivo temporal durante el deploy y lo borra al
final. Para cambiar config solo editas el secreto — no tocas el server.

**repo → Settings → Secrets and variables → Actions → New repository secret:**

| Secret           | Valor                                                        |
|------------------|--------------------------------------------------------------|
| `PROD_ENV_FILE`  | El contenido **completo** del `.env` de producción (pega todo)|

Debe incluir `APP_KEY`, `DB_*`, `REDIS_*`, `APP_PORT`, etc. Para cambiar una
variable: editas el secreto y haces push (o corres el workflow manual).

`GITHUB_TOKEN` es automático (no hay que crearlo); da `packages:write` para
publicar/leer en GHCR — ya está declarado en el workflow.

> La imagen en GHCR es privada por defecto. El runner hace `docker login ghcr.io`
> con `GITHUB_TOKEN` dentro del job, así que el pull funciona sin config extra.
> Si prefieres, puedes hacer el paquete público en GitHub Packages.

---

## 3. Primer despliegue

Haz push a `dev` (para probar) o `main`, o corre el workflow manual desde la
pestaña **Actions**. El job `build` publica la imagen y `deploy` la levanta en
el server. Flujo normal: iteras en `dev` → cuando queda bien, merge a `main`.

Rollback: en Actions, re-ejecuta un run anterior (más simple), **o** en el
server pineando un SHA viejo:

```bash
APP_IMAGE=ghcr.io/almexwebapps/almex_maintenance_tracker_admin:<SHA_ANTERIOR> \
  docker compose -f docker-compose.portainer.yml --env-file <tu-env> up -d
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
