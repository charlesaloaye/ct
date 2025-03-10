import { testimonies } from "@/constants/const"
import TestimonyCard from "./shared/TestimonyCard"

const Testimonies = () => {
  return (
    <section className="section testimonials-section">
            <div className="container">
                <div className="section-heading">
                    <h3>
                        <span>Testimonials</span>
                    </h3>
                </div>
                <div className="owl-carousel" data-items="3" data-nav-dots="true" data-lg-items="3" data-md-items="2" data-sm-items="1" data-xs-items="1" data-space="24" data-autoplay="false">
                     {testimonies.map((testimony, index) => (
                              <TestimonyCard
                              key={index}
                              client={testimony.client}
                                title={testimony.title}
                                review={testimony.review}
                                image={testimony.image}
                                role={testimony.role}
                              />
                            ))}
                   
                </div>
            </div>
        </section>
  )
}

export default Testimonies
