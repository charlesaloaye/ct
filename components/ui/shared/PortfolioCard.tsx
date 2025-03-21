import Image from "next/image";
import Link from "next/link";

interface PortfolioCardProps {
  title: string;
  description: string;
  icon: React.ReactNode;
  image: string;
  url: string;
}

const PortfolioCard: React.FC<PortfolioCardProps> = ({
  title,
  description,
  icon,
  image,
  url,
}) => {
  return (
    <div className="col-sm-6 col-lg-4 grid-item">
      <div className="portfolio-box">
        <div className="portfolio-text">
          <h6>{title}</h6>
          <p>{description}</p>
          <Link className="gallery-link" href={image}>
            {icon}
          </Link>
        </div>
        <div className="portfolio-img">
          <Image className="w-full h-auto" src={image} title={title} alt={title} height={250} width={500} />
          <Link className="px_modal portfolio-modal-link" href={url}>
            {icon}
          </Link>
        </div>
      </div>
    </div>
  );
};

export default PortfolioCard;
