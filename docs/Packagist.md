# Publishing gbrain/excel-imports to Packagist

1. **Push the repository to GitHub (or GitLab/Bitbucket)**  
   - Ensure `composer.json` is at the project root.  
   - Confirm the `name` is `gbrain/excel-imports`.  
   - Commit and push all changes.

2. **Tag a version**  
   In your local clone:

   ```bash
   git tag v0.1.0
   git push --tags
   ```

   Use [Semantic Versioning](https://semver.org/) for future versions.

3. **Submit to Packagist**  
   - Go to https://packagist.org  
   - Log in (or create an account).  
   - Click “Submit” and paste the repository URL.  
   - Packagist will read `composer.json` and register the package.

4. **Configure Auto-Updates**  
   After submitting, Packagist can be hooked to GitHub webhooks so new tags
   are picked up automatically.

5. **Require the package in projects**  

   ```bash
   composer require gbrain/excel-imports
   ```
