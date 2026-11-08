import SiteNav from '../components/SiteNav.jsx';
import { SimpleFooter } from '../components/Footers.jsx';
import { FlashToast, Seo } from '../components/Site.jsx';
import { FeaturedProject, ProjectCard } from '../components/Projects.jsx';

/** Every published project: the featured one first, then the rest in their homepage order. */
export default function Work({ projects, sections }) {
  const copy = sections.workPage;
  const featured = projects.find((p) => p.featured);
  const rest = projects.filter((p) => p !== featured);

  return (
    <>
      <Seo />
      <SiteNav />
      <main>
        <section className="container blog-hero">
          <div className="blog-hero-copy">
            {copy.eyebrow && <span className="eyebrow">{copy.eyebrow}</span>}
            {copy.title && <h1 className="blog-title">{copy.title}</h1>}
            {copy.lead && <p className="blog-lead">{copy.lead}</p>}
          </div>
        </section>
        <section className="container section-md" style={{ paddingTop: 8 }}>
          {featured && <FeaturedProject featured={featured} />}
          {rest.length > 0 && (
            <div className="project-grid">
              {rest.map((p) => <ProjectCard key={p.id} p={{ ...p, showOnMobile: true }} />)}
            </div>
          )}
          {projects.length === 0 && <p className="empty">Projects are on their way.</p>}
        </section>
      </main>
      <SimpleFooter />
      <FlashToast />
    </>
  );
}
