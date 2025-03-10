import { projects } from "@/constants/const"
import PortfolioCard from "./shared/PortfolioCard"

const Portfolio = () => {
  return (
    <section id="portfolio" data-scroll-index="3" className="section portfolio-section gray-bg-1">
    <div className="container">
        <div className="section-heading">
            <h3>
                <span>My Portfolio</span>
            </h3>
        </div>
        <div className="lightbox-gallery">
        <div className="row g-3 g-lg-4 portfolio-content">
     {projects.map((service, index) => (
              <PortfolioCard
              key={index}
              id={index}
              image={service.image}
                title={service.title}
                description={service.description}
                icon={service.icon}
                url={service.url}
              />
            ))}
    </div>
    </div>
    </div>
</section>
  )
}

export default Portfolio
