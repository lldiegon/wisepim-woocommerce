#!/usr/bin/env python3
"""Build a deterministic plugin ZIP from an exact, reviewed runtime manifest."""
from pathlib import Path
import argparse
import hashlib
import os
import re
import tempfile
import zipfile

ROOT = Path(__file__).resolve().parents[1]
SLUG = 'wisepim-for-woocommerce'
# Adding runtime files is an explicit release decision. Do not recurse folders:
# developer notes, diagnostics, or credential-bearing PHP must never ride along.
RUNTIME_FILES = (
    'LICENSE',
    'assets/admin.css',
    'assets/owl-connect.webp',
    'assets/wisepim-logo.png',
    'includes/class-wisepim-woocommerce-plugin.php',
    'includes/view-admin.php',
    'readme.txt',
    'uninstall.php',
    'wisepim-for-woocommerce.php',
)


def read_runtime(root: Path) -> dict:
    contents = {}
    for name in RUNTIME_FILES:
        relative = Path(name)
        if relative.is_absolute() or '..' in relative.parts:
            raise ValueError('Unsafe runtime manifest path')
        path = root / relative
        ancestors = [root.joinpath(*relative.parts[:i]) for i in range(1, len(relative.parts) + 1)]
        if any(part.is_symlink() for part in ancestors) or not path.is_file() or not path.resolve().is_relative_to(root.resolve()):
            raise ValueError('Missing or unsafe runtime path: ' + name)
        contents[name] = path.read_bytes()
    return contents


def build(root: Path, output: Path) -> str:
    contents = read_runtime(root)
    main = contents['wisepim-for-woocommerce.php'].decode('utf-8')
    readme = contents['readme.txt'].decode('utf-8')
    version = re.search(r'^\s*\* Version:\s*(\S+)', main, re.M)
    stable = re.search(r'^Stable tag:\s*(\S+)', readme, re.M)
    runtime = re.search(r"define\(\s*'WISEPIM_WOOCOMMERCE_VERSION'\s*,\s*'([^']+)'\s*\)", main)
    if not version or not stable or not runtime or not (version[1] == stable[1] == runtime[1]):
        raise ValueError('Plugin Version, runtime constant, and readme Stable tag must match')
    if not re.fullmatch(r'(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)\.(?:0|[1-9]\d*)(?:-[0-9A-Za-z]+(?:[.-][0-9A-Za-z]+)*)?', version[1]):
        raise ValueError('Plugin version must be a semantic version')
    checksum_path = output.with_suffix('.zip.sha256')
    protected = { (root / name).resolve() for name in RUNTIME_FILES }
    if output.is_symlink() or checksum_path.is_symlink() or output.resolve() in protected or checksum_path.resolve() in protected:
        raise ValueError('Unsafe output path')
    output.parent.mkdir(parents=True, exist_ok=True)
    # Validate/read everything first, then atomically replace the finished archive.
    # A failed build cannot truncate an existing release or leave a partial ZIP.
    descriptor, temporary = tempfile.mkstemp(prefix='.plugin-build-', dir=output.parent)
    os.close(descriptor)
    temporary_path = Path(temporary)
    try:
        with zipfile.ZipFile(temporary_path, 'w', zipfile.ZIP_DEFLATED, compresslevel=9) as archive:
            for name, data in sorted(contents.items()):
                info = zipfile.ZipInfo(SLUG + '/' + name, date_time=(2020, 1, 1, 0, 0, 0))
                info.compress_type = zipfile.ZIP_DEFLATED
                info.create_system = 3
                info.external_attr = 0o100644 << 16
                archive.writestr(info, data, compresslevel=9)
        checksum = hashlib.sha256(temporary_path.read_bytes()).hexdigest()
        temporary_path.replace(output)
        checksum_path.write_text(f'{checksum}  {output.name}\n')
    finally:
        temporary_path.unlink(missing_ok=True)
    return version[1]


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--output', type=Path, default=ROOT / 'dist' / (SLUG + '.zip'))
    args = parser.parse_args()
    version = build(ROOT, args.output)
    print(f'Built {args.output} (v{version})')
