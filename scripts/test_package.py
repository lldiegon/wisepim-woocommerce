#!/usr/bin/env python3
"""Regression checks for release contents, version mismatch, and reproducibility."""
from pathlib import Path
import shutil
import tempfile
import unittest
import zipfile
from package import ROOT, SLUG, build


class PackageTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory()
        self.addCleanup(self.temp.cleanup)
        self.root = Path(self.temp.name) / 'source'
        shutil.copytree(ROOT, self.root, ignore=shutil.ignore_patterns('.git', '.env', '.env.*', '.internal', 'outputs', 'dist', 'node_modules', 'vendor', '__pycache__'))
        self.output = Path(self.temp.name) / 'plugin.zip'

    def test_zip_is_installable_allowlisted_and_reproducible(self):
        (self.root / '.env').write_text('SECRET=never-ship')
        (self.root / 'assets' / '.secret.php').write_text('never-ship')
        build(self.root, self.output)
        first = self.output.read_bytes()
        build(self.root, self.output)
        self.assertEqual(first, self.output.read_bytes())
        with zipfile.ZipFile(self.output) as archive:
            names = set(archive.namelist())
            self.assertIn(SLUG + '/wisepim-for-woocommerce.php', names)
            self.assertIn(SLUG + '/readme.txt', names)
            self.assertIn(SLUG + '/LICENSE', names)
            self.assertIn(SLUG + '/uninstall.php', names)
            self.assertTrue(all(name.startswith(SLUG + '/') for name in names))
            self.assertFalse(any('/tests/' in name or '/scripts/' in name or '/.git' in name or '/docs/' in name or '.secret' in name or '.env' in name for name in names))
            self.assertIsNone(archive.testzip())

    def test_mismatched_version_fails(self):
        path = self.root / 'readme.txt'
        path.write_text(path.read_text().replace('Stable tag: 0.1.0', 'Stable tag: 999.0.0'))
        with self.assertRaisesRegex(ValueError, 'must match'):
            build(self.root, self.output)

    def test_symlink_runtime_file_fails(self):
        external = Path(self.temp.name) / 'external.php'
        external.write_text('private')
        (self.root / 'includes' / 'unsafe.php').symlink_to(external)
        with self.assertRaisesRegex(ValueError, 'unsafe'):
            build(self.root, self.output)


if __name__ == '__main__':
    unittest.main()
