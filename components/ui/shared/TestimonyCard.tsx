const TestimonyCard = ({role, image, title, review, client, }) => {
  return (
    <div className="testimonials-box mb-1">
                        <div className="t-lead">
                            <img src={image} title={title} alt={title} height={250} width={250}/>
                        </div>
                        <div className="t-text">
                            <h5>{title}</h5>
                            <p>{review}</p>
                            <div className="t-avatar">
                                <h6>{client}</h6>
                                <span>{role}</span>
                            </div>
                        </div>
                    </div>
  )
} 
export default TestimonyCard
