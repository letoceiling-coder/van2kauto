import { Container } from "@/components/layout/Container";
import { site } from "@/content/site-data";

export function Footer() {
  return (
    <footer data-component-id="layout.footer" className="border-t border-border py-10 text-sm text-muted-foreground">
      <Container className="flex flex-col justify-between gap-4 sm:flex-row">
        <p>
          © {new Date().getFullYear()} {site.name}
        </p>
        <p>{site.description}</p>
      </Container>
    </footer>
  );
}
