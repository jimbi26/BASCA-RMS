Vercel Docker Deployment (notes)
=================================

This project can be deployed to Vercel using a Docker container. Vercel container deployments typically require a Business plan — verify your account before proceeding.

What I added
- `Dockerfile` — container image using `php:8.2-apache` with `pdo_pgsql`, `gd`, and `curl` installed.
- `vercel.json` — switched to the Docker builder (`@vercel/docker`).

Before you deploy on Vercel
1. Upgrade your Vercel plan if required (container deployments often need paid plan).
2. In Vercel → Project → Environment Variables, add these variables (mark for Production):
   - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`
   - `SUPABASE_URL`, `SUPABASE_SERVICE_KEY`, `SUPABASE_BUCKET`
   - `BASE_URL` (optional)

Deploy steps
1. Push these commits to GitHub (already pushed).
2. In Vercel, ensure the project is linked to this GitHub repo and branch.
3. Trigger a redeploy (Vercel will build the Docker image).

Testing after deploy
- PHP runtime test: `https://<your-vercel-domain>/api/test.php`
- DB test: `https://<your-vercel-domain>/api/db-test.php`
- Storage test: `https://<your-vercel-domain>/api/storage-test.php`

Viewing build logs
- In Vercel dashboard: Project → Deployments → Select latest deployment → View Build Logs.
- The previous failure was: "The package `@vercel/php` is not published on the npm registry" — that occurs when using a non-existent Vercel builder.

If Docker on Vercel is not an option
- I recommend deploying to Render or Railway (both support native PHP web services on free/low-cost tiers). I can prepare Render instructions if you want.
