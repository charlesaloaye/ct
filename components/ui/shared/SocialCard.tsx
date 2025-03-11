import Link from "next/link";

interface SocialCardsProps {
  title: string;
  icon: React.ReactNode;
  url: string;
  target?: "_blank" | "_self" | "_parent" | "_top";
}

const SocialCards: React.FC<SocialCardsProps> = ({ title, icon, url, target = "_self" }) => {
  return (
      <li>
        <div className="a-icon">{icon}</div>
        <div className="a-text">
          <Link target={target} className="text-reset stretched-link" href={url}>
            {title}
          </Link>
        </div>
      </li>
  );
};

export default SocialCards;
