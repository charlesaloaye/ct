
const ServiceCard = ({title, icon, description}) => {
  return (
    <div className="col-sm-6 col-lg-4 mb-1">
        <div className="feature-box-01">
            <div className="icon">{icon}</div>
            <div className="feature-content">
                <h5>{title}</h5>
                <p>{description}</p>
            </div>
        </div>
</div>
  )
}

export default ServiceCard
