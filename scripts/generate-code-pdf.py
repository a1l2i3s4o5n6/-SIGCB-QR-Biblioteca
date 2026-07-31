#!/usr/bin/env python3
"""
Generates CODIGO-FUENTE.pdf with selected source files from SIGCB-QR.
Uses Pygments for syntax highlighting and fpdf2 for PDF generation.
"""

import os
import sys
from pathlib import Path

from fpdf import FPDF
from pygments import lex
from pygments.lexers import JavaLexer, SqlLexer
from pygments.token import Token

ROOT = Path(__file__).resolve().parent.parent
OUTPUT = ROOT / "docs" / "codigo" / "CODIGO-FUENTE.pdf"
FONTS_DIR = Path("C:/Windows/Fonts")

FILES = [
    # Security
    ("sigcb-qr-api/src/main/java/com/sigcbqr/security/JwtTokenProvider.java", JavaLexer),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/security/JwtAuthenticationFilter.java", JavaLexer),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/config/SecurityConfig.java", JavaLexer),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/security/JwtBlacklistService.java", JavaLexer),
    # Auth
    ("sigcb-qr-api/src/main/java/com/sigcbqr/controller/AuthController.java", JavaLexer),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/service/AuthService.java", JavaLexer),
    # Prestamos
    ("sigcb-qr-api/src/main/java/com/sigcbqr/controller/PrestamoController.java", JavaLexer),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/service/PrestamoService.java", JavaLexer),
    # Cache & Errors
    ("sigcb-qr-api/src/main/java/com/sigcbqr/config/CacheConfig.java", JavaLexer),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/exception/GlobalExceptionHandler.java", JavaLexer),
    # Entities
    ("sigcb-qr-api/src/main/java/com/sigcbqr/model/entity/Prestamo.java", JavaLexer),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/model/entity/Libro.java", JavaLexer),
    ("sigcb-qr-api/src/main/java/com/sigcbqr/model/entity/Usuario.java", JavaLexer),
    # Stored Procedures
    ("db/procs/sp_crear_prestamo.sql", SqlLexer),
    ("db/procs/sp_devolver_prestamo.sql", SqlLexer),
    ("db/procs/sp_reporte_prestamos_diarios.sql", SqlLexer),
]

TOKEN_COLORS = {
    Token.Comment: (0, 128, 0),
    Token.Comment.Single: (0, 128, 0),
    Token.Comment.Multiline: (0, 128, 0),
    Token.Keyword: (0, 0, 255),
    Token.Keyword.Type: (0, 0, 255),
    Token.Keyword.Declaration: (0, 0, 255),
    Token.Keyword.Namespace: (0, 0, 255),
    Token.String: (163, 21, 21),
    Token.String.Double: (163, 21, 21),
    Token.String.Single: (163, 21, 21),
    Token.Number: (0, 0, 128),
    Token.Number.Integer: (0, 0, 128),
    Token.Number.Float: (0, 0, 128),
    Token.Name.Decorator: (128, 0, 128),
    Token.Name.Annotation: (128, 0, 128),
    Token.Name.Function: (128, 0, 0),
    Token.Name.Class: (128, 0, 128),
    Token.Name.Builtin: (0, 128, 128),
    Token.Operator: (0, 0, 0),
    Token.Punctuation: (64, 64, 64),
    Token.Text: (0, 0, 0),
    Token.Literal: (163, 21, 21),
}


def get_color(token_type, default=(0, 0, 0)):
    for ttype, color in TOKEN_COLORS.items():
        if token_type in ttype:
            return color
    return default


class CodePDF(FPDF):
    def __init__(self):
        super().__init__(orientation="P", unit="mm", format="A4")
        self.set_auto_page_break(auto=True, margin=15)

        # Register Unicode TrueType fonts from Windows
        sans_path = FONTS_DIR / "DejaVuSans.ttf"
        sans_bold_path = FONTS_DIR / "DejaVuSans-Bold.ttf"
        mono_path = FONTS_DIR / "DejaVuSansMono.ttf"
        mono_bold_path = FONTS_DIR / "DejaVuSansMono-Bold.ttf"

        self.add_font("DejaVu", "", str(sans_path))
        self.add_font("DejaVu", "B", str(sans_bold_path))
        self.add_font("DejaVuMono", "", str(mono_path))
        self.add_font("DejaVuMono", "B", str(mono_bold_path))

        self.code_size = 6.5
        self.line_h = 3.5

    def header(self):
        if self.page_no() > 1:
            self.set_font("DejaVu", "", 7)
            self.set_text_color(128, 128, 128)
            self.cell(0, 4, "SIGCB-QR  -  Codigo Fuente  |  Pagina %d" % self.page_no(), align="R")
            self.ln(6)

    def footer(self):
        pass

    def cover_page(self):
        self.add_page()
        self.ln(60)
        self.set_font("DejaVu", "B", 28)
        self.set_text_color(0, 51, 102)
        self.cell(0, 14, "SIGCB-QR", align="C")
        self.ln(12)
        self.set_font("DejaVu", "", 18)
        self.set_text_color(60, 60, 60)
        self.cell(0, 10, "Sistema de Gestion de Biblioteca", align="C")
        self.ln(20)
        self.set_font("DejaVu", "B", 22)
        self.set_text_color(0, 0, 0)
        self.cell(0, 12, "CODIGO FUENTE", align="C")
        self.ln(8)
        self.set_font("DejaVu", "", 11)
        self.set_text_color(100, 100, 100)
        self.cell(0, 7, "Entregable Tercera Entrega  -  v0.9.0-rc", align="C")
        self.ln(5)
        self.cell(0, 7, "Julio 2026", align="C")

    def summary_page(self):
        self.add_page()
        self.set_font("DejaVu", "B", 14)
        self.set_text_color(0, 51, 102)
        self.cell(0, 8, "Archivos incluidos", new_x="LMARGIN")
        self.ln(10)

        self.set_font("DejaVuMono", "", 8)
        self.set_text_color(0, 0, 0)
        for i, (rel_path, _) in enumerate(FILES, 1):
            self.cell(0, 5, "%2d.  %s" % (i, rel_path.replace("/", "\\")), new_x="LMARGIN")
            self.ln(5)

        self.ln(5)
        self.set_font("DejaVu", "", 9)
        self.set_text_color(100, 100, 100)
        self.cell(0, 5, "Total: %d archivos" % len(FILES), new_x="LMARGIN")

    def write_code_file(self, rel_path, lexer_cls):
        full_path = ROOT / rel_path
        if not full_path.exists():
            return

        self.add_page()

        self.set_font("DejaVu", "B", 10)
        self.set_text_color(0, 51, 102)
        self.cell(0, 6, rel_path.replace("/", "\\"), new_x="LMARGIN")
        self.ln(8)

        code = full_path.read_text(encoding="utf-8")
        lines = code.split("\n")

        if lines and lines[-1] == "":
            lines = lines[:-1]

        line_width = len(str(len(lines)))
        linenum_w = line_width * 2.5 + 5
        margin_left = self.l_margin
        margin_right = self.r_margin
        page_w = self.w - margin_left - margin_right
        col_width = page_w - linenum_w - 2

        self.set_font("DejaVuMono", "", self.code_size)

        for line_no, line in enumerate(lines, 1):
            # Check if we need a new page
            if self.get_y() > self.h - self.b_margin - self.line_h * 2:
                self.add_page()
                self.set_font("DejaVu", "B", 10)
                self.set_text_color(0, 51, 102)
                self.cell(0, 6, rel_path.replace("/", "\\") + " (cont.)", new_x="LMARGIN")
                self.ln(6)
                self.set_font("DejaVuMono", "", self.code_size)

            x_start = self.get_x()

            # Line number
            self.set_text_color(150, 150, 150)
            self.cell(linenum_w + 1, self.line_h, str(line_no).rjust(line_width), align="R")

            if line.strip() == "":
                self.cell(col_width, self.line_h, "")
                self.ln()
                continue

            tokens = list(lex(line + "\n", lexer_cls()))
            if tokens and tokens[-1][1] == "\n":
                tokens = tokens[:-1]

            for token_type, token_value in tokens:
                if not token_value:
                    continue
                r, g, b = get_color(token_type)
                self.set_text_color(r, g, b)
                self.cell(self.get_string_width(token_value), self.line_h, token_value)

            self.ln()
            self.set_x(x_start)


def main():
    pdf = CodePDF()

    print("Generating cover page...")
    pdf.cover_page()

    print("Generating summary...")
    pdf.summary_page()

    for i, (rel_path, lexer_cls) in enumerate(FILES, 1):
        full_path = ROOT / rel_path
        if not full_path.exists():
            print("  [SKIP] %s (not found)" % rel_path)
            continue
        print("  [%d/%d] %s" % (i, len(FILES), rel_path))
        pdf.write_code_file(rel_path, lexer_cls)

    OUTPUT.parent.mkdir(parents=True, exist_ok=True)
    pdf.output(str(OUTPUT))
    print("\nPDF generated: %s" % OUTPUT)
    print("Pages: %d" % pdf.page_no())


if __name__ == "__main__":
    main()
