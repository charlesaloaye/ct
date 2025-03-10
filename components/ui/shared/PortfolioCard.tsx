import { url } from "inspector"
import Image from "next/image"
import Link from "next/link"

const PortfolioCard = ({id, title, description, icon, image, url}) => {
  return (
    <>
  
                <div className="col-sm-6 col-lg-4 grid-item" id={id}>
                    <div className="portfolio-box">
                        <div className="portfolio-text">
                            <h6>{title}</h6>
                            <p>{description}</p>
                            <Link className="gallery-link" href={`${image}`}>{icon}</Link>
                        </div>
                        <div className="portfolio-img">
                            <Image src={`${image}`} title={title} alt={title} height={250} width={500}/>
                            <Link className="px_modal portfolio-modal-link" href={url}>
                                {icon}
                            </Link>
                        </div>
                    </div>
                </div>
               
     
        </>
  )
}

export default PortfolioCard
