# v2.0 Branching Strategy

```bash
git checkout master
git pull origin master
git tag -a v1.0-rc1 -m "Lotto ERP Enterprise v1.0 RC1"
git push origin v1.0-rc1

git checkout -b develop/v2.0
git push -u origin develop/v2.0
```

Feature branches must be created from `develop/v2.0`:

```bash
git checkout develop/v2.0
git checkout -b feature/api-v2
```
