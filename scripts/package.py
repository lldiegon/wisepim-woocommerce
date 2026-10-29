#!/usr/bin/env python3
"""Build an installable, deterministic plugin ZIP from an explicit runtime allowlist."""
from pathlib import Path
import argparse
import hashlib
import re
import zipfile

ROOT = Path(__file__).resolve().parents[1]
SLUG = 'wisepim-for-woocommerce'
REQUIRED = ('wisepim-for-woocommerce.php', 'uninstall.php', 'readme.txt', 'LICENSE')
RUNTIME_DIRS = ('includes', 'assets', 'languages')
EXTENSIONS = {'.php', '.css', '.js', '.svg', '.png', '.jpg', '.jpeg', '.webp', '.gif', '.pot', '.po', '.mo'}


def build(root: Path, output: Path) -> str:
    main = (root / REQUIRED[0]).read_text()
    readme = (root / 'readme.txt').read_text()
    version = re.search(r'^\s*\* Version:\s*(\S+)', main, re.M)
    stable = re.search(r'^Stable tag:\s*(\S+)', readme, re.M)
    if not version or not stable or version[1] != stable[1]:
        raise ValueError('Plugin Version and readme Stable tag must match')
    files = [root / name for name in REQUIRED]
    for directory in RUNTIME_DIRS:
        folder = root / directory
        if folder.exists():
            files.extend(p for p in folder.rglob('*') if p.is_file() and p.suffix.lower() in EXTENSIONS and not any(part.startswith('.') for part in p.relative_to(root).parts))
    output.parent.mkdir(parents=True, exist_ok=True)
    with zipfile.ZipFile(output, 'w', zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
        for path in sorted(files):
            if not path.is_file() or path.is_symlink() or not path.resolve().is_relative_to(root.resolve()):
                raise ValueError('Missing or unsafe runtime path: ' + str(path))
            info = zipfile.ZipInfo(SLUG + '/' + path.relative_to(root).as_posix(), date_time=(2020, 1, 1, 0, 0, 0))
            info.compress_type = zipfile.ZIP_DEFLATED
            info.create_system = 3
            info.external_attr = 0o100644 << 16
            archive.writestr(info, path.read_bytes(), compresslevel=9)
    checksum = hashlib.sha256(output.read_bytes()).hexdigest()
    output.with_suffix('.zip.sha256').write_text(f'{checksum}  {output.name}\n')
    return version[1]


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output', type=Path, default=ROOT / 'dist' / (SLUG + '.zip'))
    args = parser.parse_args()
    version = build(ROOT, args.output)
    print(f'Built {args.output} (v{version})')
