#!/usr/bin/env python3
"""Adversarial release-boundary checks: contents, paths, versions, and atomicity."""
from pathlib import Path
import hashlib
import shutil
import tempfile
import unittest
import zipfile
from package import ROOT, SLUG, RUNTIME_FILES, build


class PackageTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.addCleanup(self.temp.cleanup)
        self.root = Path(self.temp.name) / 'source'
        shutil.copytree(ROOT, self.root, ignore=shutil.ignore_patterns('.git', '.env', '.env.*', '.internal', 'outputs', 'dist', 'node_modules', 'vendor', '__pycache__'))
        self.output = Path(self.temp.name) / 'plugin.zip'

    def test_zip_has_exact_manifest_and_is_reproducible(self):
        for name in ('.env', '.internal/strategy.md', 'assets/.secret.php', 'assets/diagnostics.js', 'includes/private-config.php', 'includes/backup.php', 'assets/merchant-export.csv'):
            path = self.root / name
            path.parent.mkdir(parents=True, exist_ok=True)
            path.write_text('DO-NOT-SHIP')
        build(self.root, self.output)
        first = self.output.read_bytes()
        build(self.root, self.output)
        self.assertEqual(first, self.output.read_bytes())
        with zipfile.ZipFile(self.output) as archive:
            self.assertEqual(set(archive.namelist()), {SLUG + '/' + name for name in RUNTIME_FILES})
            self.assertTrue(all(b'DO-NOT-SHIP' not in archive.read(name) for name in archive.namelist()))
            self.assertIsNone(archive.testzip())
        self.assertEqual(self.output.with_suffix('.zip.sha256').read_text(), hashlib.sha256(first).hexdigest() + '  plugin.zip\n')

    def test_readme_version_mismatch_fails(self):
        path = self.root / 'readme.txt'
        import re
        path.write_text(re.sub(r'^Stable tag: .+$', 'Stable tag: 999.0.0', path.read_text(), flags=re.M))
        with self.assertRaisesRegex(ValueError, 'must match'):
            build(self.root, self.output)

    def test_runtime_constant_mismatch_fails(self):
        path = self.root / 'wisepim-for-woocommerce.php'
        import re
        path.write_text(re.sub(r"(define\(\s*'WISEPIM_WOOCOMMERCE_VERSION'\s*,\s*')[^']+", r'\g<1>999.0.0', path.read_text()))
        with self.assertRaisesRegex(ValueError, 'must match'):
            build(self.root, self.output)

    def test_symlink_runtime_file_fails(self):
        external = Path(self.temp.name) / 'external.php'
        external.write_text('private')
        path = self.root / 'includes/view-admin.php'
        path.unlink()
        path.symlink_to(external)
        with self.assertRaisesRegex(ValueError, 'unsafe'):
            build(self.root, self.output)

    def test_symlink_runtime_directory_inside_repository_fails(self):
        private = self.root / '.internal/runtime-copy'
        private.parent.mkdir()
        (self.root / 'includes').rename(private)
        (self.root / 'includes').symlink_to(private, target_is_directory=True)
        with self.assertRaisesRegex(ValueError, 'unsafe'):
            build(self.root, self.output)

    def test_symlink_output_cannot_overwrite_other_file(self):
        private = Path(self.temp.name) / 'private.txt'
        private.write_text('preserve')
        self.output.symlink_to(private)
        with self.assertRaisesRegex(ValueError, 'Unsafe output'):
            build(self.root, self.output)
        self.assertEqual(private.read_text(), 'preserve')

    def test_symlink_checksum_cannot_overwrite_other_file(self):
        private = Path(self.temp.name) / 'private.txt'
        private.write_text('preserve')
        self.output.with_suffix('.zip.sha256').symlink_to(private)
        with self.assertRaisesRegex(ValueError, 'Unsafe output'):
            build(self.root, self.output)
        self.assertEqual(private.read_text(), 'preserve')

    def test_source_cannot_be_used_as_output(self):
        with self.assertRaisesRegex(ValueError, 'Unsafe output'):
            build(self.root, self.root / 'wisepim-for-woocommerce.php')

    def test_failed_build_preserves_existing_archive(self):
        build(self.root, self.output)
        before = self.output.read_bytes()
        (self.root / 'assets/admin.css').unlink()
        with self.assertRaisesRegex(ValueError, 'Missing'):
            build(self.root, self.output)
        self.assertEqual(self.output.read_bytes(), before)


if __name__ == '__main__':
    unittest.main()
